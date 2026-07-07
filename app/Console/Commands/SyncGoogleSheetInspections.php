<?php

namespace App\Console\Commands;

use App\Models\Department;
use App\Models\Equipment;
use App\Models\Insp;
use App\Models\InspItem;
use App\Models\SyncAuditLog;
use App\Models\SyncLog;
use Google\Client as GoogleClient;
use Google\Service\Sheets;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use RuntimeException;
use Throwable;

class SyncGoogleSheetInspections extends Command
{
    private const array INSPS_COLUMNS = [
        'insp_id',
        'insp_no',
        'insp_dep_id',
        'insp_eqm_id',
        'is_submitted',
        'checklist_template_name',
        'checklist_temp_items',
        'insp_remarks',
        'insp_by',
        'insp_submitted_by',
        'insp_submitted_at',
    ];

    private const array INSP_ITEMS_COLUMNS = [
        'inspi_id',
        'inspi_insp_id',
        'inspi_task',
        'inspi_result',
        'inspi_remarks',
    ];

    protected $signature = 'sync:google-sheet-inspections
        {--insps-sheet=insps : Google Sheet tab for inspections}
        {--insp-items-sheet=insp_items : Google Sheet tab for inspection items}';

    protected $description = 'Append submitted inspections and related inspection items from Google Sheet into insps/insp_items';

    public function handle(): int
    {
        $inspsSheet = (string) $this->option('insps-sheet');
        $inspItemsSheet = (string) $this->option('insp-items-sheet');

        try {
            [$inspsRows, $inspsLastRow] = $this->fetchSheetRows($inspsSheet, self::INSPS_COLUMNS);
            [$inspItemsRows, $inspItemsLastRow] = $this->fetchSheetRows($inspItemsSheet, self::INSP_ITEMS_COLUMNS);

            $insertedInspections = 0;
            $insertedInspectionItems = 0;
            $appendedInspectionIds = [];

            DB::transaction(function () use (
                $inspsRows,
                $inspItemsRows,
                &$insertedInspections,
                &$insertedInspectionItems,
                &$appendedInspectionIds
            ): void {
                $appendedInspectionIds = $this->appendSubmittedInspections($inspsRows);
                $insertedInspections = count($appendedInspectionIds);

                if ($appendedInspectionIds === []) {
                    return;
                }

                $insertedInspectionItems = $this->appendRelatedInspectionItems($inspItemsRows, $appendedInspectionIds);
            });

            $this->updateSyncLog($inspsSheet, $inspsLastRow);
            $this->updateSyncLog($inspItemsSheet, $inspItemsLastRow);

            $this->writeAuditLog($inspsSheet, $insertedInspections, $inspsLastRow, 'success');
            $this->writeAuditLog($inspItemsSheet, $insertedInspectionItems, $inspItemsLastRow, 'success');

            $this->info(sprintf(
                'Sync complete. Inserted %d inspections and %d inspection items.',
                $insertedInspections,
                $insertedInspectionItems
            ));

            return self::SUCCESS;
        } catch (Throwable $exception) {
            Log::error('Google Sheet inspection sync failed', [
                'message' => $exception->getMessage(),
            ]);

            $this->writeAuditLog(
                $inspsSheet,
                0,
                null,
                'failed',
                $exception->getMessage()
            );

            $this->writeAuditLog(
                $inspItemsSheet,
                0,
                null,
                'failed',
                $exception->getMessage()
            );

            $this->error('Sync failed: '.$exception->getMessage());

            return self::FAILURE;
        }
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @return list<string>
     */
    private function appendSubmittedInspections(array $rows): array
    {
        $submittedRows = array_values(array_filter(
            $rows,
            fn (array $row): bool => $this->isNotBlank($row['is_submitted'] ?? null)
        ));

        if ($submittedRows === []) {
            return [];
        }

        $candidateIds = array_values(array_filter(
            array_map(
                fn (array $row): string => trim((string) ($row['insp_id'] ?? '')),
                $submittedRows
            ),
            fn (string $id): bool => $id !== ''
        ));

        if ($candidateIds === []) {
            return [];
        }

        $existingIds = Insp::query()
            ->whereIn('insp_id', $candidateIds, 'and', false)
            ->pluck('insp_id')
            ->all();

        $existingLookup = array_fill_keys($existingIds, true);
        $appendedInspectionIds = [];

        foreach ($submittedRows as $row) {
            $inspId = trim((string) ($row['insp_id'] ?? ''));

            if ($inspId === '' || isset($existingLookup[$inspId])) {
                continue;
            }

            Insp::query()->create([
                'insp_id' => $inspId,
                'insp_no' => $this->normalizeNullableString($row['insp_no'] ?? null),
                'insp_dep_id' => $this->resolveDepartmentId($row['insp_dep_id'] ?? null),
                'insp_eqm_id' => $this->resolveEquipmentId($row['insp_eqm_id'] ?? null),
                'is_submitted' => $this->normalizeNullableString($row['is_submitted'] ?? null),
                'checklist_template_name' => $this->normalizeNullableString($row['checklist_template_name'] ?? null),
                'checklist_temp_items' => $this->normalizeNullableString($row['checklist_temp_items'] ?? null),
                'insp_remarks' => $this->normalizeNullableString($row['insp_remarks'] ?? null),
                'insp_by' => $this->normalizeNullableString($row['insp_by'] ?? null),
                'insp_submitted_by' => $this->normalizeNullableString($row['insp_submitted_by'] ?? null),
                'insp_submitted_at' => $this->normalizeDateTime($row['insp_submitted_at'] ?? null),
            ]);

            $existingLookup[$inspId] = true;
            $appendedInspectionIds[] = $inspId;
        }

        return $appendedInspectionIds;
    }

    /**
     * @param  list<array<string, string|null>>  $rows
     * @param  list<string>  $appendedInspectionIds
     */
    private function appendRelatedInspectionItems(array $rows, array $appendedInspectionIds): int
    {
        if ($rows === [] || $appendedInspectionIds === []) {
            return 0;
        }

        $appendedLookup = array_fill_keys($appendedInspectionIds, true);

        $relatedRows = array_values(array_filter($rows, function (array $row) use ($appendedLookup): bool {
            $inspectionId = trim((string) ($row['inspi_insp_id'] ?? ''));

            return $inspectionId !== '' && isset($appendedLookup[$inspectionId]);
        }));

        if ($relatedRows === []) {
            return 0;
        }

        $candidateIds = array_values(array_filter(
            array_map(
                fn (array $row): string => trim((string) ($row['inspi_id'] ?? '')),
                $relatedRows
            ),
            fn (string $id): bool => $id !== ''
        ));

        if ($candidateIds === []) {
            return 0;
        }

        $existingIds = InspItem::query()
            ->whereIn('inspi_id', $candidateIds, 'and', false)
            ->pluck('inspi_id')
            ->all();

        $existingLookup = array_fill_keys($existingIds, true);
        $insertedCount = 0;

        foreach ($relatedRows as $row) {
            $itemId = trim((string) ($row['inspi_id'] ?? ''));

            if ($itemId === '' || isset($existingLookup[$itemId])) {
                continue;
            }

            InspItem::query()->create([
                'inspi_id' => $itemId,
                'inspi_insp_id' => trim((string) ($row['inspi_insp_id'] ?? '')),
                'inspi_task' => $this->normalizeNullableString($row['inspi_task'] ?? null),
                'inspi_result' => $this->normalizeInspectionResult($row['inspi_result'] ?? null),
                'inspi_remarks' => $this->normalizeNullableString($row['inspi_remarks'] ?? null),
            ]);

            $existingLookup[$itemId] = true;
            $insertedCount++;
        }

        return $insertedCount;
    }

    /**
     * @param  list<string>  $expectedColumns
     * @return array{0: list<array<string, string|null>>, 1: int|null}
     */
    private function fetchSheetRows(string $sheetName, array $expectedColumns): array
    {
        $service = $this->createSheetsService();
        $spreadsheetId = $this->spreadsheetId();

        $range = sprintf("'%s'!A1:ZZ", str_replace("'", "\\'", $sheetName));
        $response = $service->spreadsheets_values->get($spreadsheetId, $range);
        $values = $response->getValues();

        if ($values === []) {
            return [[], null];
        }

        $headerRow = $values[0] ?? [];
        $headerLookup = [];

        foreach ($headerRow as $index => $header) {
            $headerLookup[strtolower(trim((string) $header))] = $index;
        }

        $missingColumns = [];

        foreach ($expectedColumns as $column) {
            if (! array_key_exists(strtolower($column), $headerLookup)) {
                $missingColumns[] = $column;
            }
        }

        if ($missingColumns !== []) {
            throw new RuntimeException(sprintf(
                'Missing expected columns in sheet "%s": %s',
                $sheetName,
                implode(', ', $missingColumns)
            ));
        }

        $rows = [];

        foreach (array_slice($values, 1) as $rowValues) {
            $mapped = [];

            foreach ($expectedColumns as $column) {
                $headerIndex = $headerLookup[strtolower($column)];
                $mapped[$column] = isset($rowValues[$headerIndex])
                    ? trim((string) $rowValues[$headerIndex])
                    : null;
            }

            $rows[] = $mapped;
        }

        return [$rows, count($values)];
    }

    private function createSheetsService(): Sheets
    {
        $credentialsPath = storage_path('google-sheets-credentials.json');

        if (! is_file($credentialsPath)) {
            throw new RuntimeException('Google Sheets credentials file not found at storage/google-sheets-credentials.json');
        }

        $client = new GoogleClient;
        $client->setApplicationName('EMMS Inspection Sync');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig($credentialsPath);

        return new Sheets($client);
    }

    private function spreadsheetId(): string
    {
        $spreadsheetId = config('services.google.spreadsheet_id');

        if (! is_string($spreadsheetId) || trim($spreadsheetId) === '') {
            throw new RuntimeException('GOOGLE_SPREADSHEET_ID is not configured.');
        }

        return $spreadsheetId;
    }

    private function updateSyncLog(string $sheetName, ?int $lastRow): void
    {
        SyncLog::query()->updateOrCreate(
            ['sheet_name' => $sheetName],
            ['last_row_synced' => $lastRow ?? 1]
        );
    }

    private function writeAuditLog(
        string $sheetName,
        int $syncedRows,
        ?int $lastRowProcessed,
        string $status,
        ?string $errorMessage = null
    ): void {
        SyncAuditLog::query()->create([
            'sheet_name' => $sheetName,
            'synced_rows' => $syncedRows,
            'last_row_processed' => $lastRowProcessed,
            'status' => $status,
            'error_message' => $errorMessage,
            'synced_at' => now(),
        ]);
    }

    private function normalizeNullableString(mixed $value): ?string
    {
        $normalized = trim((string) ($value ?? ''));

        return $normalized === '' ? null : $normalized;
    }

    private function normalizeNullableInteger(mixed $value): ?int
    {
        $normalized = $this->normalizeNullableString($value);

        if ($normalized === null) {
            return null;
        }

        if (! is_numeric($normalized)) {
            return null;
        }

        return (int) $normalized;
    }

    private function resolveDepartmentId(mixed $value): ?int
    {
        $normalized = $this->normalizeNullableString($value);

        if ($normalized === null) {
            return null;
        }

        if (is_numeric($normalized)) {
            $department = Department::query()->find((int) $normalized);

            if ($department !== null) {
                return (int) $department->dep_id;
            }
        }

        $department = Department::query()
            ->whereRaw('lower(dep_code) = ?', [strtolower($normalized)], 'and')
            ->first();

        return $department !== null ? (int) $department->dep_id : null;
    }

    private function resolveEquipmentId(mixed $value): ?int
    {
        $equipmentId = $this->normalizeNullableInteger($value);

        if ($equipmentId === null) {
            return null;
        }

        return Equipment::query()->find($equipmentId) !== null
            ? $equipmentId
            : null;
    }

    private function normalizeDateTime(mixed $value): ?Carbon
    {
        $normalized = $this->normalizeNullableString($value);

        if ($normalized === null) {
            return null;
        }

        try {
            return Carbon::parse($normalized);
        } catch (Throwable) {
            return null;
        }
    }

    private function isNotBlank(mixed $value): bool
    {
        return $this->normalizeNullableString($value) !== null;
    }

    private function normalizeInspectionResult(mixed $value): ?string
    {
        $normalized = strtolower((string) $this->normalizeNullableString($value));

        return match ($normalized) {
            'passed' => 'Passed',
            'failed' => 'Failed',
            'n/a', 'na' => 'N/A',
            default => null,
        };
    }
}
