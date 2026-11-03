<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ReadsRfidToken;
use App\Jobs\ProcessRfidRawLogs;
use App\Models\Location;
use App\Models\RfidRawLog;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Receives the reads from the RFID reader server. It only stores them (rfid_raw_logs), whatever the tag is, and
 * answers straight away; ProcessRfidRawLogs then moves the reads of registered tags into the tag logs and trips.
 */
class RfidLogController extends Controller
{
    use ReadsRfidToken;

    /** 500 reads are about 60 KB; anything far bigger is not the reader. */
    private const MAX_BODY_BYTES = 262144;

    public function store(Request $request, string $yard): JsonResponse
    {
        abort_unless($request->isJson(), 415, 'Content-Type must be application/json.');
        abort_if(strlen($request->getContent()) > self::MAX_BODY_BYTES, 413, 'Request body is too large.');
        abort_unless(Location::whereKey($yard)->exists(), 404, 'Yard not found.');

        try {
            $logs = collect(Validator::validate(
                ['logs' => $request->json()->all()],
                [
                    'logs' => ['required', 'array', 'list', 'min:1', 'max:500'],
                    'logs.*.Tag' => ['required', 'string', 'max:64'],
                    'logs.*.Status' => ['required', 'string', 'regex:/^(in|out)$/i'],
                    'logs.*.Timestamp' => ['required', 'date_format:Y-m-d H:i:s'],
                ],
            )['logs']);
        } catch (ValidationException $exception) {
            // Never the body: it is whatever the caller chose to send.
            logger()->warning('RFID request rejected', [
                'ip' => $request->ip(),
                'token_id' => $this->tokenId($request),
                'bytes' => strlen($request->getContent()),
                'errors' => array_keys($exception->errors()),
            ]);

            throw $exception;
        }

        $batch = (string) Str::uuid();
        $tokenId = $this->tokenId($request);
        $ip = $request->ip();

        $rows = $logs->map(fn (array $log): array => [
            'batch_uuid' => $batch,
            'yard_id' => $yard,
            'token_id' => $tokenId,
            'ip' => $ip,
            'tag_id' => trim($log['Tag']),
            'status' => strtoupper($log['Status']),
            'detected_at' => $log['Timestamp'],
        ])->all();

        // Reads already stored (the reader retrying a batch) are skipped by the unique key.
        $inserted = RfidRawLog::query()->insertOrIgnore($rows);

        if ($inserted > 0) {
            ProcessRfidRawLogs::dispatch();
        }

        return response()->json([
            'received' => $logs->count(),
            'inserted' => $inserted,
            'not_inserted' => [],
        ]);
    }
}
