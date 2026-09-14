<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Models\AssetEditLog;
use Filament\Actions\AssociateAction;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\DissociateAction;
use Filament\Actions\DissociateBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class EditLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'editLogs';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $count = $ownerRecord->editLogs()->count();

        return (string) $count;
    }

    protected static function renderValue(string $field, mixed $value): string
    {
        if (is_array($value)) {
            if (empty($value)) {
                return '-';
            }

            return collect($value)
                ->map(fn ($item) => '<span class="inline-flex items-center rounded-full bg-gray-100 px-2 py-0.5 text-xs font-medium text-gray-700 dark:bg-white/10 dark:text-gray-300">'.e((string) $item).'</span>')
                ->implode(' ');
        }

        return e(AssetEditLog::resolveFieldValue($field, $value));
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Textarea::make('changes')
                    ->required()
                    ->columnSpanFull(),
                TextInput::make('performed_by')
                    ->numeric()
                    ->default(null),
                DateTimePicker::make('logged_at')
                    ->required(),
            ]);
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                TextEntry::make('changes')
                    ->label('Changes')
                    ->state(function ($record) {
                        // Build the full HTML string upfront: TextEntry treats array state as a
                        // comma-joined list of values and would call this per field otherwise.
                        $state = $record->changes;

                        if (empty($state)) {
                            return '-';
                        }

                        $rows = collect($state)
                            ->map(function ($value, string $field): string {
                                if (! is_array($value)) {
                                    $value = ['old' => null, 'new' => $value];
                                }

                                $column = e(AssetEditLog::fieldLabel($field));
                                $old = self::renderValue($field, $value['old'] ?? null);
                                $new = self::renderValue($field, $value['new'] ?? null);

                                return <<<HTML
                                    <tr class="border-b border-gray-200 last:border-b-0 dark:border-white/10">
                                        <td class="px-4 py-3 align-top text-sm font-medium text-gray-900 dark:text-white">{$column}</td>
                                        <td class="px-4 py-3 align-top text-sm whitespace-pre-wrap text-danger-600 dark:text-danger-400">{$old}</td>
                                        <td class="px-4 py-3 align-top text-sm whitespace-pre-wrap text-success-600 dark:text-success-400">{$new}</td>
                                    </tr>
                                HTML;
                            })
                            ->implode('');

                        return <<<HTML
                            <div class="overflow-hidden rounded-lg border border-gray-200 dark:border-white/10">
                                <table class="w-full text-left">
                                    <thead class="bg-gray-50 dark:bg-white/5">
                                        <tr>
                                            <th class="px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Column</th>
                                            <th class="px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Old Value</th>
                                            <th class="px-4 py-2 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">New Value</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        {$rows}
                                    </tbody>
                                </table>
                            </div>
                        HTML;
                    })
                    ->html()
                    ->columnSpanFull(),
                TextEntry::make('performedBy.user_email')
                    ->label('Performed By')
                    ->placeholder('System'),
                TextEntry::make('logged_at')
                    ->dateTime(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->defaultSort('logged_at', 'desc')
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('performedBy.user_email')
                    ->label('Performed By')
                    ->placeholder('System')
                    ->sortable(),
                TextColumn::make('logged_at')
                    ->dateTime()
                    ->sortable(),
            ])
            ->filters([
                //
            ])
            ->headerActions([
                CreateAction::make(),
                AssociateAction::make(),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DissociateAction::make(),
                DeleteAction::make(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DissociateBulkAction::make(),
                    DeleteBulkAction::make(),
                ]),
            ]);
    }
}
