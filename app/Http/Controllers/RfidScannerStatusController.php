<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Concerns\ReadsRfidToken;
use App\Models\Location;
use App\Models\RfidScannerStatus;
use App\Models\RfidScannerStatusLog;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

/**
 * Receives the status of a gate scanner (IN / OUT) from the RFID reader server. The reader server decides whether
 * a scanner is working; EMMS only keeps the latest status, a history of changes, and shows/alerts on it.
 */
class RfidScannerStatusController extends Controller
{
    use ReadsRfidToken;

    /** One small object; anything bigger is not the reader server. */
    private const MAX_BODY_BYTES = 4096;

    /** How far ahead of the server clock a report may be (clock drift). */
    private const FUTURE_TOLERANCE_MINUTES = 5;

    public function store(Request $request, string $yard): JsonResponse
    {
        abort_unless($request->isJson(), 415, 'Content-Type must be application/json.');
        abort_if(strlen($request->getContent()) > self::MAX_BODY_BYTES, 413, 'Request body is too large.');
        abort_unless(Location::whereKey($yard)->exists(), 404, 'Yard not found.');

        try {
            $data = Validator::validate($request->json()->all(), [
                'scannerName' => ['required', 'string', 'regex:/^(in|out)$/i'],
                'status' => ['required', 'boolean'],
                'timestamp' => ['required', 'date_format:Y-m-d H:i:s'],
            ]);

            $reportedAt = Carbon::createFromFormat('Y-m-d H:i:s', $data['timestamp']);

            // One report far in the future would otherwise make every later (real) report look "older" and be ignored.
            if ($reportedAt->gt(now()->addMinutes(self::FUTURE_TOLERANCE_MINUTES))) {
                throw ValidationException::withMessages(['timestamp' => 'The timestamp is in the future.']);
            }
        } catch (ValidationException $exception) {
            // Never the body: it is whatever the caller chose to send.
            logger()->warning('RFID scanner status rejected', [
                'ip' => $request->ip(),
                'token_id' => $this->tokenId($request),
                'bytes' => strlen($request->getContent()),
                'errors' => array_keys($exception->errors()),
            ]);

            throw $exception;
        }

        [$applied, $changed] = $this->record(
            (int) $yard,
            strtoupper($data['scannerName']),
            (bool) $data['status'],
            $reportedAt,
            $this->tokenId($request),
            $request->ip(),
        );

        return response()->json(['received' => 1, 'applied' => $applied, 'changed' => $changed]);
    }

    /**
     * @return array{0: bool, 1: bool} whether the report was applied (not older than the one already held), and whether the status changed
     */
    private function record(int $yard, string $scanner, bool $isActive, Carbon $reportedAt, ?int $tokenId, ?string $ip): array
    {
        return DB::transaction(function () use ($yard, $scanner, $isActive, $reportedAt, $tokenId, $ip): array {
            // The first report creates the row; two reports racing to be first cannot both create it.
            $created = RfidScannerStatus::query()->insertOrIgnore([
                'yard_id' => $yard,
                'scanner_name' => $scanner,
                'is_active' => $isActive,
                'reported_at' => $reportedAt,
                'status_changed_at' => $reportedAt,
            ]);

            $changed = $created > 0;
            $applied = true;

            if (! $changed) {
                $current = RfidScannerStatus::query()
                    ->where('yard_id', $yard)
                    ->where('scanner_name', $scanner)
                    ->lockForUpdate()
                    ->firstOrFail();

                if ($reportedAt->lt($current->reported_at)) {
                    // A retry or an out-of-order report: the newer one already holds. It still proves the relay is alive.
                    $applied = false;
                    RfidScannerStatus::query()->whereKey($current->getKey())->update(['last_seen_at' => DB::raw('CURRENT_TIMESTAMP')]);
                } else {
                    $changed = $current->is_active !== $isActive;

                    RfidScannerStatus::query()->whereKey($current->getKey())->update([
                        'is_active' => $isActive,
                        'reported_at' => $reportedAt,
                        'last_seen_at' => DB::raw('CURRENT_TIMESTAMP'),
                        ...($changed ? ['status_changed_at' => $reportedAt] : []),
                    ]);
                }
            }

            // History only for a first report or a change: repeats every few minutes would only fill the table.
            if ($applied && $changed) {
                RfidScannerStatusLog::query()->insert([
                    'yard_id' => $yard,
                    'scanner_name' => $scanner,
                    'is_active' => $isActive,
                    'reported_at' => $reportedAt,
                    'token_id' => $tokenId,
                    'ip' => $ip,
                ]);
            }

            return [$applied, $changed];
        });
    }
}
