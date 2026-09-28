<?php

namespace App\Mail\Concerns;

/**
 * Builds a per-work-order reply-to address so that replies from either
 * managers or technicians route back through the tracked mailbox with
 * the work order number embedded in the address (e.g.
 * "workorders+WO-PREV-260928001@domain.com"). This is what lets
 * GmailWorkOrderReplySyncService match an inbound reply to the correct
 * WorkOrder regardless of which notification email it was a reply to.
 */
trait HasWorkOrderReplyTo
{
    private function buildReplyToAddress(): ?string
    {
        $replyToAddress = config('work_orders.reply_to_address');

        if (! $replyToAddress || ! str_contains($replyToAddress, '@')) {
            return $replyToAddress;
        }

        [$localPart, $domainPart] = explode('@', $replyToAddress, 2);
        $threadToken = trim($this->workOrder->wo_no);

        if ($threadToken === '') {
            return $replyToAddress;
        }

        return sprintf('%s+%s@%s', $localPart, $threadToken, $domainPart);
    }
}
