<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Models\AssetEditLog;
use Carbon\Carbon;
use Filament\Actions\AssociateAction;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\RepeatableEntry;
use Filament\Infolists\Components\RepeatableEntry\TableColumn;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Schema;
use Filament\Support\Enums\FontWeight;
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

    /**
     * A logged value as plain text, or a list of texts for multi-value fields such as Tags.
     *
     * @return string|list<string>
     */
    protected static function plainValue(string $field, mixed $value): string|array
    {
        if (is_array($value)) {
            return $value === [] ? '-' : array_values(array_map('strval', $value));
        }

        if ($field === 'eqm_date_purchased' && filled($value)) {
            return Carbon::parse($value)->format('F d, Y');
        }

        return AssetEditLog::resolveFieldValue($field, $value);
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
                RepeatableEntry::make('change_rows')
                    ->label('Changes')
                    ->state(fn (AssetEditLog $record): array => collect($record->changes ?? [])
                        ->map(function (mixed $value, string $field): array {
                            if (! is_array($value) || ! array_key_exists('new', $value)) {
                                $value = ['old' => null, 'new' => $value];
                            }

                            return [
                                'column' => AssetEditLog::fieldLabel($field),
                                'old' => self::plainValue($field, $value['old'] ?? null),
                                'new' => self::plainValue($field, $value['new'] ?? null),
                            ];
                        })
                        ->values()
                        ->all())
                    ->table([
                        TableColumn::make('Column'),
                        TableColumn::make('Old Value'),
                        TableColumn::make('New Value'),
                    ])
                    ->schema([
                        TextEntry::make('column')
                            ->hiddenLabel()
                            ->weight(FontWeight::Medium),
                        TextEntry::make('old')
                            ->hiddenLabel()
                            ->color('danger')
                            ->listWithLineBreaks(),
                        TextEntry::make('new')
                            ->hiddenLabel()
                            ->color('success')
                            ->listWithLineBreaks(),
                    ])
                    ->placeholder('No changes recorded')
                    ->columnSpanFull(),
                TextEntry::make('performedBy.user_email')
                    ->label('Performed By')
                    ->placeholder('System'),
                TextEntry::make('logged_at')
                    ->dateTime('F d, Y h:i A'),
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
                    ->date('F d, Y h:i A')
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
            ])
            ->toolbarActions([
                // No delete or dissociate actions: edit logs are permanent history.
            ]);
    }
}
