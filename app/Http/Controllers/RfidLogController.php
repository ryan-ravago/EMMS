<?php

namespace App\Http\Controllers;

use App\Models\AssetTag;
use App\Models\AssetTagLog;
use App\Models\Location;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class RfidLogController extends Controller
{
    public function store(Request $request, string $yard): JsonResponse
    {
        abort_unless($request->isJson(), 415, 'Content-Type must be application/json.');
        abort_unless(Location::whereKey($yard)->exists(), 404, 'Yard not found.');

        $logs = collect(Validator::validate(
            ['logs' => $request->json()->all()],
            [
                'logs' => ['required', 'array', 'list', 'min:1', 'max:500'],
                'logs.*.Tag' => ['required', 'string', 'max:255'],
                'logs.*.Status' => ['required', 'string', 'regex:/^(in|out)$/i'],
                'logs.*.Timestamp' => ['required', 'date_format:Y-m-d H:i:s'],
            ],
        )['logs']);

        // lowercase => canonical tag_id (asset_tags.tag_id is the FK target)
        $knownTags = AssetTag::whereIn('tag_id', $logs->pluck('Tag')->unique())
            ->pluck('tag_id')
            ->mapWithKeys(fn (string $tag) => [strtolower($tag) => $tag]);

        // Logs already stored for this yard; also fed with the rows of this batch to catch repeats inside it.
        $seen = AssetTagLog::where('yard_id', $yard)
            ->whereIn('tag_id', $knownTags->values())
            ->whereIn('detected_at', $logs->pluck('Timestamp')->unique())
            ->toBase()
            ->get(['tag_id', 'detected_at', 'status'])
            ->mapWithKeys(fn (object $row) => [$this->logKey($row->tag_id, $row->detected_at, $row->status) => true])
            ->all();

        $rows = [];
        $notInserted = [];

        foreach ($logs as $index => $log) {
            $tag = $knownTags->get(strtolower($log['Tag']));
            $key = $tag ? $this->logKey($tag, $log['Timestamp'], $log['Status']) : null;

            $reason = match (true) {
                $tag === null => 'unknown_tag',
                isset($seen[$key]) => 'duplicate',
                default => null,
            };

            if ($reason) {
                $notInserted[] = ['index' => $index, ...$log, 'reason' => $reason];

                continue;
            }

            $seen[$key] = true;

            // rssi and created_at are intentionally left out (NULL); received_at is the DB default.
            $rows[] = [
                'detected_at' => $log['Timestamp'],
                'tag_id' => $tag,
                'yard_id' => $yard,
                'status' => strtoupper($log['Status']),
            ];
        }

        return response()->json([
            'received' => $logs->count(),
            'inserted' => AssetTagLog::insertOrIgnore($rows),
            'not_inserted' => $notInserted,
        ]);
    }

    private function logKey(string $tag, string $detectedAt, string $status): string
    {
        return strtolower($tag).'|'.$detectedAt.'|'.strtoupper($status);
    }
}
