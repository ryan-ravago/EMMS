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
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Textarea;
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
                    ->formatStateUsing(function ($state) {
                        if (empty($state)) {
                            return '-';
                        }

                        $rows = collect($state)
                            ->map(function ($value, string $field): string {
                                if (! is_array($value)) {
                                    $value = ['old' => null, 'new' => $value];
                                }

                                $column = e(AssetEditLog::fieldLabel($field));
                                $old = e(AssetEditLog::resolveFieldValue($field, $value['old'] ?? null));
                                $new = e(AssetEditLog::resolveFieldValue($field, $value['new'] ?? null));

                                return <<<HTML
                                    <tr class="border-b border-gray-200 dark:border-gray-700">
                                        <td class="px-4 py-3 text-sm font-medium text-gray-900 dark:text-white">{$column}</td>
                                        <td class="px-4 py-3 text-sm text-gray-500 dark:text-gray-400 whitespace-pre-wrap">{$old}</td>
                                        <td class="px-4 py-3 text-sm text-gray-900 dark:text-white whitespace-pre-wrap">{$new}</td>
                                    </tr>
                                HTML;
                            })
                            ->implode('');

                        return <<<HTML
                            <div class="overflow-hidden rounded-xl border border-gray-200 dark:border-gray-700">
                                <table class="w-full text-left">
                                    <thead class="bg-gray-50 dark:bg-gray-800">
                                        <tr>
                                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Column</th>
                                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">Old Value</th>
                                            <th class="px-4 py-3 text-xs font-semibold uppercase tracking-wide text-gray-500 dark:text-gray-400">New Value</th>
                                        </tr>
                                    </thead>
                                    <tbody class="divide-y divide-gray-200 dark:divide-gray-700">
                                        {$rows}
                                    </tbody>
                                </table>
                            </div>
                        HTML;
                    })
                    ->html()
                    ->columnSpanFull(),
                TextEntry::make('performedBy.user_email')
                    ->numeric()
                    ->placeholder('-'),
                TextEntry::make('logged_at')
                    ->dateTime(),
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->columns([
                TextColumn::make('performedBy.user_email')
                    ->numeric()
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
