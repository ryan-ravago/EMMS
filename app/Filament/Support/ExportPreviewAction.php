<?php

namespace App\Filament\Support;

use Filament\Actions\Action;
use Filament\Actions\Exports\ExportColumn;
use Filament\Actions\Exports\Exporter;
use Filament\Actions\Exports\Models\Export;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Contracts\HasTable;
use Illuminate\Contracts\View\View;
use Livewire\Component;

/**
 * Opens a modal with the first rows an export would contain, built from the same query
 * (table filters, search, sorting and department scoping) and the same exporter columns as
 * Filament's ExportAction, so what is previewed is what gets downloaded.
 */
class ExportPreviewAction extends Action
{
    public const PREVIEW_ROWS = 50;

    /** @var class-string<Exporter>|null */
    protected ?string $exporter = null;

    public static function getDefaultName(): ?string
    {
        return 'previewExport';
    }

    /**
     * @param  class-string<Exporter>  $exporter
     */
    public function exporter(string $exporter): static
    {
        $this->exporter = $exporter;

        return $this;
    }

    protected function setUp(): void
    {
        parent::setUp();

        $this->label('Preview')
            ->icon(Heroicon::OutlinedEye)
            ->color('gray')
            ->modalHeading('Export preview')
            ->modalWidth('7xl')
            ->modalSubmitAction(false)
            ->modalCancelActionLabel('Close')
            ->modalContent(fn (Component $livewire): View => view('filament.exports.preview', $this->preview($livewire)));
    }

    /**
     * @return array{headers: array<int, string>, rows: array<int, array<int, mixed>>, total: int, shown: int}
     */
    public function preview(Component $livewire): array
    {
        $exporter = $this->exporter;

        abort_unless($exporter !== null && $livewire instanceof HasTable, 500);

        $query = $exporter::modifyQuery($livewire->getTableQueryForExport());

        $total = (clone $query)->reorder()->count();
        $records = $query->limit(self::PREVIEW_ROWS)->get();

        $columnMap = collect($exporter::getColumns())
            ->filter(fn (ExportColumn $column): bool => $column->isEnabledByDefault())
            ->mapWithKeys(fn (ExportColumn $column): array => [$column->getName() => $column->getLabel()])
            ->all();

        $instance = new $exporter(new Export, $columnMap, []);

        return [
            'headers' => array_values($columnMap),
            'rows' => $records->map(fn ($record): array => $instance($record))->all(),
            'total' => $total,
            'shown' => $records->count(),
        ];
    }
}
