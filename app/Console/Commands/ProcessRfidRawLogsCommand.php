<?php

namespace App\Console\Commands;

use App\Services\Rfid\RawLogProcessor;
use Illuminate\Console\Command;

class ProcessRfidRawLogsCommand extends Command
{
    protected $signature = 'rfid:process-raw-logs';

    protected $description = 'Move the reads of registered tags from the raw RFID log into the tag logs and equipment trips';

    public function handle(RawLogProcessor $processor): int
    {
        $totals = $processor->run();

        $this->table(['Result', 'Count'], collect($totals)->map(fn (int $count, string $outcome): array => [$outcome, $count])->values()->all());

        return self::SUCCESS;
    }
}
