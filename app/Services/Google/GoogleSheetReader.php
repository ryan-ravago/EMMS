<?php

namespace App\Services\Google;

use Google\Client as GoogleClient;
use Google\Service\Sheets;
use RuntimeException;

/**
 * Read-only access to the AppSheet spreadsheet through the service account in
 * storage/google-sheets-credentials.json. The spreadsheet id comes from services.google.spreadsheet_id.
 */
class GoogleSheetReader
{
    private ?Sheets $service = null;

    /**
     * Returns the rows of a tab keyed by the requested column names. Blank cells are null and a row that is
     * completely empty in the requested columns is dropped (the sheets contain many padding rows).
     *
     * @param  list<string>  $columns
     * @return list<array<string, string|null>>
     */
    public function rows(string $sheetName, array $columns): array
    {
        $range = sprintf("'%s'!A1:ZZ", str_replace("'", "\\'", $sheetName));
        $values = $this->service()->spreadsheets_values->get($this->spreadsheetId(), $range)->getValues() ?? [];

        return self::mapRows($sheetName, $values, $columns);
    }

    /**
     * @param  list<list<mixed>>  $values  the raw sheet values, header row first
     * @param  list<string>  $columns
     * @return list<array<string, string|null>>
     */
    public static function mapRows(string $sheetName, array $values, array $columns): array
    {
        if ($values === []) {
            return [];
        }

        $headerLookup = [];

        foreach ($values[0] as $index => $header) {
            $headerLookup[strtolower(trim((string) $header))] = $index;
        }

        $missing = array_values(array_filter($columns, fn (string $column): bool => ! array_key_exists(strtolower($column), $headerLookup)));

        if ($missing !== []) {
            throw new RuntimeException(sprintf('Missing expected columns in sheet "%s": %s', $sheetName, implode(', ', $missing)));
        }

        $rows = [];

        foreach (array_slice($values, 1) as $rowValues) {
            $mapped = [];

            foreach ($columns as $column) {
                $cell = $rowValues[$headerLookup[strtolower($column)]] ?? null;
                $cell = $cell === null ? null : trim((string) $cell);
                $mapped[$column] = $cell === '' ? null : $cell;
            }

            if (array_filter($mapped, fn (?string $value): bool => $value !== null) !== []) {
                $rows[] = $mapped;
            }
        }

        return $rows;
    }

    private function service(): Sheets
    {
        if ($this->service !== null) {
            return $this->service;
        }

        $credentialsPath = storage_path('google-sheets-credentials.json');

        if (! is_file($credentialsPath)) {
            throw new RuntimeException('Google Sheets credentials file not found at storage/google-sheets-credentials.json');
        }

        $client = new GoogleClient;
        $client->setApplicationName('EMMS Inspection Sync');
        $client->setScopes([Sheets::SPREADSHEETS_READONLY]);
        $client->setAuthConfig($credentialsPath);

        return $this->service = new Sheets($client);
    }

    private function spreadsheetId(): string
    {
        $spreadsheetId = config('services.google.spreadsheet_id');

        if (! is_string($spreadsheetId) || trim($spreadsheetId) === '') {
            throw new RuntimeException('GOOGLE_SPREADSHEET_ID is not configured.');
        }

        return $spreadsheetId;
    }
}
