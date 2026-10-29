<?php

namespace App\Services\Aims;

use App\Models\Location;
use Generator;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;

/**
 * Carries the trips copied from AIMS to another EMMS database (production) without needing the AIMS connection.
 *
 * Ids differ between databases, so the file names things instead: the equipment by its code, a yard by its name
 * (full path), a person by email. The importing side looks them up again. The AIMS log id travels with each trip,
 * which is what makes the import safe to run twice and possible to undo.
 */
class AimsMovementCsv
{
    /** @var list<string> */
    public const COLUMNS = [
        'aims_log_id', 'equipment_code', 'time_out', 'time_in', 'yard_out', 'yard_in',
        'manual_out_by', 'manual_out_remarks', 'manual_in_by', 'manual_in_remarks',
        'acknowledged_by', 'acknowledged_at', 'acknowledgement_remarks', 'created_at',
    ];

    /** @var list<string> */
    public const USER_COLUMNS = ['email', 'first_name', 'middle_name', 'last_name'];

    /**
     * Writes every trip that came from AIMS, and the people named on them.
     *
     * @return array{trips: int, users: int, noEmail: int}
     */
    public function export(string $file, string $usersFile): array
    {
        $paths = Location::pathMap();
        $users = [];
        $noEmail = 0;
        $trips = 0;

        File::ensureDirectoryExists(dirname($file));
        $handle = fopen($file, 'w');
        fputcsv($handle, self::COLUMNS, escape: '');

        $query = DB::table('asset_movements as m')
            ->join('equipment_units as e', 'e.eqm_id', '=', 'm.asset_id')
            ->leftJoin('app_users as mo', 'mo.user_id', '=', 'm.manual_out_by')
            ->leftJoin('app_users as mi', 'mi.user_id', '=', 'm.manual_in_by')
            ->leftJoin('app_users as ak', 'ak.user_id', '=', 'm.acknowledged_by')
            ->whereNotNull('m.aims_log_id')
            ->orderBy('m.aims_log_id')
            ->select([
                'm.*', 'e.eqm_prc_code',
                'mo.user_email as mo_email', 'mo.user_fname as mo_first', 'mo.user_mname as mo_middle', 'mo.user_lname as mo_last',
                'mi.user_email as mi_email', 'mi.user_fname as mi_first', 'mi.user_mname as mi_middle', 'mi.user_lname as mi_last',
                'ak.user_email as ak_email', 'ak.user_fname as ak_first', 'ak.user_mname as ak_middle', 'ak.user_lname as ak_last',
            ]);

        foreach ($query->cursor() as $trip) {
            $emails = [];

            foreach (['mo' => 'manual_out_by', 'mi' => 'manual_in_by', 'ak' => 'acknowledged_by'] as $prefix => $column) {
                $email = $trip->{"{$prefix}_email"};

                if ($trip->{$column} !== null && blank($email)) {
                    $noEmail++;
                }

                $emails[$prefix] = blank($email) ? null : mb_strtolower(trim($email));

                if ($emails[$prefix] !== null) {
                    $users[$emails[$prefix]] = [$emails[$prefix], $trip->{"{$prefix}_first"}, $trip->{"{$prefix}_middle"}, $trip->{"{$prefix}_last"}];
                }
            }

            fputcsv($handle, [
                $trip->aims_log_id, $trip->eqm_prc_code, $trip->time_out, $trip->time_in,
                $trip->yard_out_id === null ? null : ($paths[$trip->yard_out_id] ?? null),
                $trip->yard_in_id === null ? null : ($paths[$trip->yard_in_id] ?? null),
                $emails['mo'], $emails['mo'] === null ? null : $trip->manual_out_remarks,
                $emails['mi'], $emails['mi'] === null ? null : $trip->manual_in_remarks,
                $emails['ak'], $emails['ak'] === null ? null : $trip->acknowledged_at, $emails['ak'] === null ? null : $trip->acknowledgement_remarks,
                $trip->created_at,
            ], escape: '');

            $trips++;
        }

        fclose($handle);

        $handle = fopen($usersFile, 'w');
        fputcsv($handle, self::USER_COLUMNS, escape: '');

        foreach ($users as $user) {
            fputcsv($handle, $user, escape: '');
        }

        fclose($handle);

        return ['trips' => $trips, 'users' => count($users), 'noEmail' => $noEmail];
    }

    /**
     * @return Generator<int, array<string, string>>
     */
    public function read(string $file, array $expected = self::COLUMNS): Generator
    {
        $handle = fopen($file, 'r');
        $header = array_map(fn ($h) => trim((string) preg_replace('/^\xEF\xBB\xBF/', '', (string) $h)), fgetcsv($handle, 0, ',', '"', '') ?: []);

        if (array_diff($expected, $header) !== []) {
            fclose($handle);

            throw new \InvalidArgumentException('The file is missing columns: '.implode(', ', array_diff($expected, $header)));
        }

        while (($row = fgetcsv($handle, 0, ',', '"', '')) !== false) {
            if (count(array_filter($row, fn ($v) => trim((string) $v) !== '')) === 0) {
                continue;
            }

            yield array_combine($header, array_pad($row, count($header), ''));
        }

        fclose($handle);
    }

    /**
     * Turns the file's rows into rows for asset_movements in this database.
     *
     * @param  iterable<array<string, string>>  $rows
     * @param  array<string, int>  $assetIds  lower-case equipment code => eqm_id
     * @param  array<string, int>  $userIds  lower-case email => user_id
     * @param  array<string, int>  $yardIds  upper-case location path => location id
     * @param  array<int, true>  $imported  AIMS log ids already in asset_movements
     * @return array{rows: list<array<string, mixed>>, skipped: array<string, int>, missingEquipment: list<string>, missingUsers: list<string>, missingYards: list<string>}
     */
    public function plan(iterable $rows, array $assetIds, array $userIds, array $yardIds, array $imported = []): array
    {
        $planned = [];
        $skipped = ['already imported' => 0, 'equipment not found' => 0];
        $missingEquipment = [];
        $missingUsers = [];
        $missingYards = [];

        $user = function (string $email) use ($userIds, &$missingUsers): ?int {
            $email = mb_strtolower(trim($email));

            if ($email === '') {
                return null;
            }

            if (! isset($userIds[$email])) {
                $missingUsers[$email] = true;

                return null;
            }

            return $userIds[$email];
        };
        $yard = function (string $path) use ($yardIds, &$missingYards): ?int {
            $key = mb_strtoupper(trim($path));

            if ($key === '') {
                return null;
            }

            if (! isset($yardIds[$key])) {
                $missingYards[$key] = true;

                return null;
            }

            return $yardIds[$key];
        };
        $blank = fn (mixed $value): ?string => blank($value) ? null : (string) $value;

        foreach ($rows as $row) {
            $logId = (int) $row['aims_log_id'];
            $code = mb_strtolower(trim($row['equipment_code']));

            if (isset($imported[$logId])) {
                $skipped['already imported']++;

                continue;
            }

            if (! isset($assetIds[$code])) {
                $skipped['equipment not found']++;
                $missingEquipment[$row['equipment_code']] = true;

                continue;
            }

            $manualOut = $user($row['manual_out_by']);
            $manualIn = $user($row['manual_in_by']);
            $acknowledgedBy = $user($row['acknowledged_by']);

            $planned[] = [
                'asset_id' => $assetIds[$code],
                'tag_id' => null,
                'out_log_id' => null,
                'in_log_id' => null,
                'aims_log_id' => $logId,
                'time_out' => $blank($row['time_out']),
                'time_in' => $blank($row['time_in']),
                'yard_out_id' => $yard($row['yard_out']),
                'yard_in_id' => $yard($row['yard_in']),
                'manual_out_by' => $manualOut,
                'manual_out_at' => null,
                'manual_out_remarks' => $manualOut === null ? null : $blank($row['manual_out_remarks']),
                'manual_in_by' => $manualIn,
                'manual_in_at' => null,
                'manual_in_remarks' => $manualIn === null ? null : $blank($row['manual_in_remarks']),
                'acknowledged_by' => $acknowledgedBy,
                'acknowledged_at' => $acknowledgedBy === null ? null : $blank($row['acknowledged_at']),
                'acknowledgement_remarks' => $acknowledgedBy === null ? null : $blank($row['acknowledgement_remarks']),
                'disregarded_by' => null,
                'disregarded_at' => null,
                'disregard_remarks' => null,
                'created_at' => $blank($row['created_at']),
                'updated_at' => $blank($row['created_at']),
            ];
        }

        return [
            'rows' => $planned,
            'skipped' => $skipped,
            'missingEquipment' => array_keys($missingEquipment),
            'missingUsers' => array_keys($missingUsers),
            'missingYards' => array_keys($missingYards),
        ];
    }
}
