<?php

namespace App\Services\Google;

use App\Models\AppUser;
use App\Models\WorkOrder;
use App\Models\WorkOrderLogUpdate;
use Google\Client as GoogleClient;
use Google\Service\Gmail;
use Google\Service\Gmail\ModifyMessageRequest;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use RuntimeException;

class GmailWorkOrderReplySyncService
{
    public function __construct(
        private readonly GmailReplyParser $parser,
    ) {}

    public function syncMailbox(AppUser $mailboxUser): int
    {
        $gmail = new Gmail($this->createClient($mailboxUser));

        $messages = $gmail->users_messages->listUsersMessages('me', [
            'q' => 'is:unread newer_than:7d',
            'maxResults' => 50,
        ]);

        $processed = 0;

        foreach (($messages->getMessages() ?? []) as $message) {
            if ($this->processMessage($gmail, $message->getId())) {
                $processed++;
            }
        }

        return $processed;
    }

    public function processMessage(Gmail $gmail, string $messageId): bool
    {
        $payload = $gmail->users_messages->get('me', $messageId, ['format' => 'full']);
        $message = json_decode(json_encode($payload), true, flags: JSON_THROW_ON_ERROR);
        $headers = Arr::get($message, 'payload.headers', []);
        $subject = $this->parser->headerValue($headers, 'Subject') ?? '';
        $from = $this->parser->headerValue($headers, 'From') ?? '';
        $senderEmail = $this->parser->extractSenderEmail($from);
        $workOrderNo = $this->parser->extractWorkOrderNoFromHeaders($headers);
        $replyBody = $this->parser->extractBody($message);
        $attachments = $this->parser->extractAttachments($message);
        $gmailMessageId = (string) Arr::get($message, 'id');
        $gmailThreadId = (string) Arr::get($message, 'threadId');

        if (! $senderEmail || ! $workOrderNo || $replyBody === '' || $gmailMessageId === '') {
            return false;
        }

        $workOrder = WorkOrder::query()
            ->with('workers')
            ->where('wo_no', $workOrderNo)
            ->first();

        if (! $workOrder || $workOrder->wo_status_id !== 'inprog') {
            return false;
        }

        $sender = AppUser::query()
            ->whereRaw('lower(user_email) = ?', [Str::lower($senderEmail)], 'and')
            ->first();

        if (! $sender || ! $workOrder->workers->contains('user_id', $sender->user_id)) {
            return false;
        }

        DB::transaction(function () use ($gmail, $gmailMessageId, $gmailThreadId, $replyBody, $sender, $senderEmail, $subject, $workOrder, $attachments): void {
            $storedAttachments = $this->storeAttachments($gmail, $gmailMessageId, $workOrder, $attachments);

            WorkOrderLogUpdate::query()->firstOrCreate(
                ['wolu_gmail_message_id' => $gmailMessageId],
                [
                    'wolu_wo_id' => $workOrder->wo_id,
                    'wolu_update_note' => $replyBody,
                    'wolu_attachments' => $storedAttachments !== [] ? $storedAttachments : null,
                    'wolu_by' => $sender->user_id,
                    'wolu_dt' => now(),
                    'wolu_source' => 'gmail',
                    'wolu_gmail_thread_id' => $gmailThreadId,
                    'wolu_sender_email' => $senderEmail,
                    'wolu_reply_subject' => $subject,
                    'wolu_reply_token' => $workOrder->wo_no,
                ]
            );

            $gmail->users_messages->modify('me', $gmailMessageId, new ModifyMessageRequest([
                'removeLabelIds' => ['UNREAD'],
            ]));
        });

        return true;
    }

    /**
     * @param  array<int, array<string, mixed>>  $attachments
     * @return array<int, string>
     */
    private function storeAttachments(Gmail $gmail, string $messageId, WorkOrder $workOrder, array $attachments): array
    {
        $storedAttachments = [];

        foreach (array_values($attachments) as $index => $attachment) {
            $content = $this->resolveAttachmentContent($gmail, $messageId, $attachment);

            if ($content === null || $content === '') {
                continue;
            }

            $fileName = $this->buildAttachmentFileName($attachment, $index + 1);
            $path = sprintf(
                'work-order-replies/%s/%s/%s',
                $workOrder->wo_no,
                $messageId,
                $fileName
            );

            Storage::disk('local')->put($path, $content);

            $storedAttachments[] = $path;
        }

        return $storedAttachments;
    }

    /**
     * @param  array<string, mixed>  $attachment
     */
    private function resolveAttachmentContent(Gmail $gmail, string $messageId, array $attachment): ?string
    {
        $inlineData = trim((string) ($attachment['data'] ?? ''));

        if ($inlineData !== '') {
            return $this->parser->base64UrlDecode($inlineData);
        }

        $attachmentId = trim((string) ($attachment['attachment_id'] ?? ''));

        if ($attachmentId === '') {
            return null;
        }

        $attachmentBody = $gmail->users_messages_attachments->get('me', $messageId, $attachmentId);
        $data = method_exists($attachmentBody, 'getData') ? $attachmentBody->getData() : null;

        return $data ? $this->parser->base64UrlDecode((string) $data) : null;
    }

    /**
     * @param  array<string, mixed>  $attachment
     */
    private function buildAttachmentFileName(array $attachment, int $index): string
    {
        $filename = basename(trim((string) ($attachment['filename'] ?? '')));
        $mimeType = strtolower((string) ($attachment['mime_type'] ?? ''));

        if ($filename === '') {
            $filename = match ($mimeType) {
                'image/jpeg' => 'image.jpg',
                'image/png' => 'image.png',
                'image/gif' => 'image.gif',
                'image/webp' => 'image.webp',
                'application/pdf' => 'document.pdf',
                default => 'attachment.bin',
            };
        }

        $extension = pathinfo($filename, PATHINFO_EXTENSION);
        $baseName = pathinfo($filename, PATHINFO_FILENAME);
        $safeBaseName = Str::slug($baseName) ?: 'attachment';

        if ($extension === '') {
            $extension = match ($mimeType) {
                'image/jpeg' => 'jpg',
                'image/png' => 'png',
                'image/gif' => 'gif',
                'image/webp' => 'webp',
                'application/pdf' => 'pdf',
                default => 'bin',
            };
        }

        return sprintf('%02d-%s.%s', $index, $safeBaseName, $extension);
    }

    private function createClient(AppUser $mailboxUser): GoogleClient
    {
        if (! $mailboxUser->google_refresh_token) {
            throw new RuntimeException('Google refresh token is missing for the configured mailbox user.');
        }

        $client = new GoogleClient;
        $client->setApplicationName(config('app.name'));
        $client->setClientId(config('services.google.client_id'));
        $client->setClientSecret(config('services.google.client_secret'));
        $client->setScopes(config('work_orders.google_scopes'));
        $client->setAccessType('offline');

        if ($mailboxUser->google_access_token) {
            $client->setAccessToken([
                'access_token' => $mailboxUser->google_access_token,
                'refresh_token' => $mailboxUser->google_refresh_token,
                'expires_in' => $mailboxUser->google_token_expires_at
                    ? max(0, now()->diffInSeconds($mailboxUser->google_token_expires_at, false))
                    : 0,
            ]);
        }

        if ($client->isAccessTokenExpired()) {
            $refreshedToken = $client->fetchAccessTokenWithRefreshToken($mailboxUser->google_refresh_token);

            if (isset($refreshedToken['error'])) {
                throw new RuntimeException('Unable to refresh the Google access token.');
            }

            $mailboxUser->forceFill([
                'google_access_token' => $refreshedToken['access_token'] ?? null,
                'google_token_expires_at' => now()->addSeconds((int) ($refreshedToken['expires_in'] ?? 3600)),
            ])->save();

            $client->setAccessToken($refreshedToken);
        }

        return $client;
    }
}
