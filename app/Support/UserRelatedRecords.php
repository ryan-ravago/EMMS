<?php

namespace App\Support;

use App\Models\AppUser;
use Illuminate\Support\Arr;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;

/**
 * Finds the records that point at a user (work orders, logs, inspections, ...), read from the
 * database's foreign keys so new tables are picked up automatically. A user with any of these
 * can't be deleted: RESTRICT keys would make the delete fail, CASCADE keys would silently take
 * the records with it, and SET NULL keys would erase who did the work.
 */
class UserRelatedRecords
{
    /** Tables whose rows only exist for the account itself and are removed with it. */
    private const OWN_TABLES = ['exports'];

    /** Columns that hold a user id without a database foreign key. */
    private const UNCONSTRAINED_REFERENCES = [
        'equipment_task_checklist_templates' => ['etct_created_by'],
    ];

    /** Readable names for tables whose name doesn't read well on its own (singular). */
    private const LABELS = [
        'equipment_tasks_schedules' => 'equipment task schedule',
    ];

    /** @var array<string, list<string>>|null */
    private static ?array $references = null;

    /**
     * @return array<string, int> table => number of rows that reference the user
     */
    public static function for(AppUser $user): array
    {
        return self::countsFor([$user->getKey()])[$user->getKey()] ?? [];
    }

    /**
     * @param  array<int, int|string>  $userIds
     * @return array<int, array<string, int>> user id => [table => rows]; users without related records are left out
     */
    public static function countsFor(array $userIds): array
    {
        $userIds = array_values(array_unique($userIds));
        $counts = [];

        if ($userIds === []) {
            return $counts;
        }

        foreach (self::references() as $table => $columns) {
            if (count($columns) === 1) {
                $rows = DB::table($table)
                    ->whereIn($columns[0], $userIds)
                    ->select($columns[0])
                    ->selectRaw('count(*) as aggregate')
                    ->groupBy($columns[0])
                    ->pluck('aggregate', $columns[0]);

                foreach ($rows as $userId => $total) {
                    $counts[(int) $userId][$table] = (int) $total;
                }

                continue;
            }

            // Several columns (e.g. inspections.ins_by and ins_submitted_by): count each row once.
            foreach ($userIds as $userId) {
                $total = DB::table($table)
                    ->where(function ($query) use ($columns, $userId): void {
                        foreach ($columns as $column) {
                            $query->orWhere($column, $userId);
                        }
                    })
                    ->count();

                if ($total > 0) {
                    $counts[(int) $userId][$table] = $total;
                }
            }
        }

        return $counts;
    }

    /**
     * @param  array<string, int>  $counts
     */
    public static function describe(array $counts): string
    {
        $parts = collect($counts)
            ->map(fn (int $total, string $table): string => $total.' '.Str::plural(self::label($table), $total))
            ->values()
            ->all();

        return Arr::join($parts, ', ', ' and ');
    }

    /**
     * @return array<string, list<string>> table => columns that hold a user id
     */
    public static function references(): array
    {
        if (self::$references !== null) {
            return self::$references;
        }

        $references = [];

        foreach (self::foreignKeysToUsers() as [$table, $column]) {
            if (! in_array($table, self::OWN_TABLES, true)) {
                $references[$table][] = $column;
            }
        }

        foreach (self::UNCONSTRAINED_REFERENCES as $table => $columns) {
            foreach ($columns as $column) {
                if (Schema::hasColumn($table, $column)) {
                    $references[$table][] = $column;
                }
            }
        }

        ksort($references);

        return self::$references = array_map(fn (array $columns): array => array_values(array_unique($columns)), $references);
    }

    public static function flushReferences(): void
    {
        self::$references = null;
    }

    /**
     * @return list<array{0: string, 1: string}> [table, column] pairs
     */
    private static function foreignKeysToUsers(): array
    {
        $usersTable = (new AppUser)->getTable();

        // One information_schema query; Laravel's per-table lookup takes seconds on MariaDB.
        if (in_array(DB::connection()->getDriverName(), ['mysql', 'mariadb'], true)) {
            return array_map(
                fn (object $row): array => [$row->table_name, $row->column_name],
                DB::select(
                    'select TABLE_NAME as table_name, COLUMN_NAME as column_name
                     from information_schema.KEY_COLUMN_USAGE
                     where TABLE_SCHEMA = database()
                       and REFERENCED_TABLE_SCHEMA = database()
                       and REFERENCED_TABLE_NAME = ?',
                    [$usersTable],
                ),
            );
        }

        $pairs = [];

        foreach (Schema::getTableListing(Schema::getCurrentSchemaListing(), false) as $table) {
            foreach (Schema::getForeignKeys($table) as $foreignKey) {
                if ($foreignKey['foreign_table'] === $usersTable && count($foreignKey['columns']) === 1) {
                    $pairs[] = [$table, $foreignKey['columns'][0]];
                }
            }
        }

        return $pairs;
    }

    private static function label(string $table): string
    {
        return self::LABELS[$table] ?? str_replace('_', ' ', Str::singular($table));
    }
}
