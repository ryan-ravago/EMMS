<?php

namespace App\Services\Google;

class GmailReplyParser
{
    public function extractAttachments(array $payload): array
    {
        $attachments = [];

        $this->collectAttachments($payload['payload'] ?? [], $attachments);

        return $attachments;
    }

    public function extractWorkOrderNo(?string $subject): ?string
    {
        return $this->extractWorkOrderNoFromText($subject);
    }

    /**
     * @param  array<int, array<string, mixed>>  $headers
     */
    public function extractWorkOrderNoFromHeaders(array $headers): ?string
    {
        foreach (['To', 'Delivered-To', 'X-Original-To', 'Reply-To'] as $headerName) {
            $headerValue = $this->headerValue($headers, $headerName);

            if ($headerValue) {
                $workOrderNo = $this->extractWorkOrderNoFromText($headerValue);

                if ($workOrderNo) {
                    return $workOrderNo;
                }
            }
        }

        return null;
    }

    public function extractSenderEmail(?string $fromHeader): ?string
    {
        if (! $fromHeader) {
            return null;
        }

        if (preg_match('/<([^>]+)>/', $fromHeader, $matches)) {
            return strtolower(trim($matches[1]));
        }

        return str_contains($fromHeader, '@') ? strtolower(trim($fromHeader)) : null;
    }

    public function cleanReplyBody(string $body): string
    {
        $normalized = trim(str_replace(["\r\n", "\r"], "\n", $body));
        $marker = config('work_orders.reply_marker', '--- Please reply above this line ---');

        if ($marker && str_contains($normalized, $marker)) {
            $normalized = trim(explode($marker, $normalized, 2)[0]);
        }

        foreach (["\nOn ", "\nFrom:", "\n-----Original Message-----", "\nSent from my iPhone"] as $delimiter) {
            $position = strpos($normalized, $delimiter);

            if ($position !== false) {
                $normalized = trim(substr($normalized, 0, $position));
            }
        }

        return trim($normalized);
    }

    public function base64UrlDecode(string $value): string
    {
        return (string) base64_decode(strtr($value, '-_', '+/'));
    }

    public function extractBody(array $payload): string
    {
        $text = $this->extractBodyFromParts($payload['payload'] ?? []);

        if ($text !== '') {
            return $this->cleanReplyBody($text);
        }

        return $this->cleanReplyBody((string) ($payload['snippet'] ?? ''));
    }

    private function extractBodyFromParts(array $part): string
    {
        if ($part === []) {
            return '';
        }

        if (($part['mimeType'] ?? null) === 'text/plain' && isset($part['body']['data'])) {
            return $this->base64UrlDecode((string) $part['body']['data']);
        }

        if (($part['mimeType'] ?? null) === 'text/html' && isset($part['body']['data'])) {
            return trim(strip_tags(html_entity_decode($this->base64UrlDecode((string) $part['body']['data']))));
        }

        foreach ($part['parts'] ?? [] as $nestedPart) {
            $body = $this->extractBodyFromParts($nestedPart);

            if ($body !== '') {
                return $body;
            }
        }

        return '';
    }

    private function collectAttachments(array $part, array &$attachments): void
    {
        if ($part === []) {
            return;
        }

        $mimeType = strtolower((string) ($part['mimeType'] ?? ''));
        $filename = trim((string) ($part['filename'] ?? ''));
        $body = $part['body'] ?? [];
        $attachmentId = trim((string) ($body['attachmentId'] ?? ''));
        $data = trim((string) ($body['data'] ?? ''));

        $isAttachment = $mimeType !== ''
            && ! str_starts_with($mimeType, 'multipart/')
            && ! in_array($mimeType, ['text/plain', 'text/html'], true)
            && ($attachmentId !== '' || $filename !== '' || $data !== '');

        if ($isAttachment) {
            $attachments[] = [
                'mime_type' => $mimeType,
                'filename' => $filename,
                'attachment_id' => $attachmentId !== '' ? $attachmentId : null,
                'data' => $data !== '' ? $data : null,
            ];
        }

        foreach ($part['parts'] ?? [] as $nestedPart) {
            $this->collectAttachments($nestedPart, $attachments);
        }
    }

    public function headerValue(array $headers, string $name): ?string
    {
        foreach ($headers as $header) {
            if (strcasecmp((string) ($header['name'] ?? ''), $name) === 0) {
                return (string) ($header['value'] ?? '');
            }
        }

        return null;
    }

    private function extractWorkOrderNoFromText(?string $text): ?string
    {
        if (! $text) {
            return null;
        }

        if (preg_match('/\[WO:([^\]]+)\]/i', $text, $matches)) {
            return trim($matches[1]);
        }

        if (preg_match('/\bWO-[A-Z0-9-]+\b/i', $text, $matches)) {
            return trim($matches[0]);
        }

        return null;
    }
}
