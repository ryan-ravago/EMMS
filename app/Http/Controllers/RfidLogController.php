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
                'logs' => ['required', 'array', 'min:1', 'max:500'],
                'logs.*.Tag' => ['required', 'string', 'max:255'],
                'logs.*.Status' => ['required', 'string', 'regex:/^(in|out)$/i'],
                'logs.*.Timestamp' => ['required', 'date_format:Y-m-d H:i:s'],
            ],
        )['logs']);

        // lowercase => canonical tag_id (asset_tags.tag_id is the FK target)
        $knownTags = AssetTag::whereIn('tag_id', $logs->pluck('Tag')->unique())
            ->pluck('tag_id')
            ->mapWithKeys(fn (string $tag) => [strtolower($tag) => $tag]);

        [$known, $unknown] = $logs->partition(
            fn (array $log) => $knownTags->has(strtolower($log['Tag']))
        );

        // rssi and created_at are intentionally left out (NULL); received_at is the DB default.
        $inserted = AssetTagLog::insertOrIgnore(
            $known->map(fn (array $log) => [
                'detected_at' => $log['Timestamp'],
                'tag_id' => $knownTags[strtolower($log['Tag'])],
                'yard_id' => $yard,
                'status' => strtoupper($log['Status']),
            ])->all()
        );

        return response()->json([
            'received' => $logs->count(),
            'inserted' => $inserted,
            'duplicates' => $known->count() - $inserted,
            'unknown_tags' => $unknown->pluck('Tag')->unique()->values(),
        ]);
    }
}
