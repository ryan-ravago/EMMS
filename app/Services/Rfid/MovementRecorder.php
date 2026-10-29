<?php

namespace App\Services\Rfid;

use App\Models\AssetMovement;
use App\Models\AssetTagLog;
use Illuminate\Database\Eloquent\Collection as EloquentCollection;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Turns raw tag scans (asset_tag_logs) into equipment trips (asset_movements).
 *
 * One trip per equipment, not per tag: equipment carries several tags and a reader may scan them one after
 * another, so scans of the same equipment closer together than config('rfid.movement_window_seconds') belong
 * to one trip. Only equipment is tracked; accessories are ignored.
 *
 *  - OUT: opens a trip. An older trip that never got its IN is left as it is ("missing time in").
 *  - IN: closes the latest open trip, or creates a trip with only a time in ("missing time out").
 *
 * Safe to run again over the same scans: a scan that is already part of a trip is skipped.
 */
class MovementRecorder
{
    /**
     * @param  iterable<AssetTagLog>  $logs
     * @return array{opened: int, closed: int, in_only: int, ignored: int, skipped: int}
     */
    public function record(iterable $logs): array
    {
        $logs = EloquentCollection::make($logs)->sortBy([['detected_at', 'asc'], ['id', 'asc']])->values();
        $logs->loadMissing('tag.asset.assetType');

        $result = ['opened' => 0, 'closed' => 0, 'in_only' => 0, 'ignored' => 0, 'skipped' => 0];

        DB::transaction(function () use ($logs, &$result): void {
            $alreadyRecorded = AssetMovement::query()
                ->whereIn('out_log_id', $logs->pluck('id'))
                ->pluck('out_log_id')
                ->merge(AssetMovement::query()->whereIn('in_log_id', $logs->pluck('id'))->pluck('in_log_id'))
                ->flip();

            foreach ($logs as $log) {
                $asset = $log->tag?->asset;

                if ($alreadyRecorded->has($log->id) || $asset === null || ! $asset->isEquipmentAsset()) {
                    $result['skipped']++;

                    continue;
                }

                $outcome = $log->status === 'OUT' ? $this->recordOut($log) : $this->recordIn($log);
                $result[$outcome]++;
            }
        });

        return $result;
    }

    /**
     * @return 'opened'|'ignored'
     */
    private function recordOut(AssetTagLog $log): string
    {
        $assetId = $log->tag->asset_parent_id;

        $sameTrip = AssetMovement::query()
            ->whereNull('disregarded_at')
            ->where('asset_id', $assetId)
            ->whereBetween('time_out', $this->window($log))
            ->exists();

        if ($sameTrip) {
            return 'ignored';
        }

        AssetMovement::query()->create([
            'asset_id' => $assetId,
            'tag_id' => $log->tag_id,
            'out_log_id' => $log->id,
            'time_out' => $log->detected_at,
            'yard_out_id' => $log->yard_id,
        ]);

        return 'opened';
    }

    /**
     * @return 'closed'|'in_only'|'ignored'
     */
    private function recordIn(AssetTagLog $log): string
    {
        $assetId = $log->tag->asset_parent_id;

        $sameTrip = AssetMovement::query()
            ->whereNull('disregarded_at')
            ->where('asset_id', $assetId)
            ->whereBetween('time_in', $this->window($log))
            ->exists();

        if ($sameTrip) {
            return 'ignored';
        }

        $open = AssetMovement::query()
            ->open()
            ->where('asset_id', $assetId)
            ->where('time_out', '<=', $log->detected_at)
            ->orderByDesc('time_out')
            ->first();

        if ($open !== null) {
            $open->update([
                'in_log_id' => $log->id,
                'time_in' => $log->detected_at,
                'yard_in_id' => $log->yard_id,
            ]);

            return 'closed';
        }

        AssetMovement::query()->create([
            'asset_id' => $assetId,
            'tag_id' => $log->tag_id,
            'in_log_id' => $log->id,
            'time_in' => $log->detected_at,
            'yard_in_id' => $log->yard_id,
        ]);

        return 'in_only';
    }

    /**
     * @return array{0: Carbon, 1: Carbon}
     */
    private function window(AssetTagLog $log): array
    {
        $seconds = (int) config('rfid.movement_window_seconds');

        return [$log->detected_at->copy()->subSeconds($seconds), $log->detected_at->copy()->addSeconds($seconds)];
    }
}
