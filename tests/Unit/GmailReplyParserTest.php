<?php

namespace Tests\Unit;

use App\Services\Google\GmailReplyParser;
use Tests\TestCase;

class GmailReplyParserTest extends TestCase
{
    public function test_it_extracts_the_work_order_number_from_the_subject(): void
    {
        $parser = new GmailReplyParser;

        $this->assertSame(
            'WO-TEST-0001',
            $parser->extractWorkOrderNo('[WO:WO-TEST-0001] Action Required')
        );
    }

    public function test_it_extracts_the_work_order_number_from_recipient_headers(): void
    {
        $parser = new GmailReplyParser;

        $this->assertSame(
            'WO-TEST-0001',
            $parser->extractWorkOrderNoFromHeaders([
                ['name' => 'To', 'value' => 'emms-noreply+WO-TEST-0001@ravago.com.ph'],
            ])
        );
    }

    public function test_it_extracts_the_sender_email_from_the_from_header(): void
    {
        $parser = new GmailReplyParser;

        $this->assertSame(
            'ryan.masungsong@ravago.com.ph',
            $parser->extractSenderEmail('Ryan Masungsong <ryan.masungsong@ravago.com.ph>')
        );
    }

    public function test_it_removes_reply_markers_and_quoted_history(): void
    {
        $parser = new GmailReplyParser;

        $body = <<<'TEXT'
Please schedule this tonight.

--- Please reply above this line ---
On Tue, someone wrote:
> previous thread
TEXT;

        $this->assertSame(
            'Please schedule this tonight.',
            $parser->cleanReplyBody($body)
        );
    }

    public function test_it_extracts_plain_text_from_a_gmail_payload(): void
    {
        $parser = new GmailReplyParser;
        $encoded = rtrim(strtr(base64_encode("Need more materials.\n\n--- Please reply above this line ---\nOn Tue, someone wrote:\n> old reply"), '+/', '-_'), '=');

        $payload = [
            'payload' => [
                'mimeType' => 'multipart/alternative',
                'parts' => [
                    [
                        'mimeType' => 'text/plain',
                        'body' => [
                            'data' => $encoded,
                        ],
                    ],
                ],
            ],
            'snippet' => 'fallback',
        ];

        $this->assertSame(
            'Need more materials.',
            $parser->extractBody($payload)
        );
    }

    public function test_it_extracts_attachment_metadata_from_a_gmail_payload(): void
    {
        $parser = new GmailReplyParser;

        $payload = [
            'payload' => [
                'mimeType' => 'multipart/mixed',
                'parts' => [
                    [
                        'mimeType' => 'text/plain',
                        'body' => [
                            'data' => 'VGV4dCBib2R5',
                        ],
                    ],
                    [
                        'mimeType' => 'image/png',
                        'filename' => 'photo.png',
                        'body' => [
                            'attachmentId' => 'ATTACHMENT-123',
                        ],
                    ],
                ],
            ],
        ];

        $this->assertSame([
            [
                'mime_type' => 'image/png',
                'filename' => 'photo.png',
                'attachment_id' => 'ATTACHMENT-123',
                'data' => null,
            ],
        ], $parser->extractAttachments($payload));
    }
}
