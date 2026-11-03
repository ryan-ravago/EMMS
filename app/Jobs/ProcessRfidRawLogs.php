<?php

namespace App\Jobs;

use App\Services\Rfid\RawLogProcessor;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldBeUnique;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;

/**
 * Moves the reads of registered tags from rfid_raw_logs into asset_tag_logs and the trips. Dispatched after every
 * reader request; one is enough at a time, and `rfid:process-raw-logs` (scheduled every minute) catches up on anything missed.
 */
class ProcessRfidRawLogs implements ShouldBeUnique, ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public int $uniqueFor = 120;

    /**
     * @return list<int>
     */
    public function backoff(): array
    {
        return [10, 60];
    }

    public function handle(RawLogProcessor $processor): void
    {
        $processor->run();
    }
}
