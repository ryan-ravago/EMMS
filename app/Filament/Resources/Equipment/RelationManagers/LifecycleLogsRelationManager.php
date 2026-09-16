<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use Filament\Actions\BulkActionGroup;
use Filament\Actions\CreateAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Model;

class LifecycleLogsRelationManager extends RelationManager
{
    protected static string $relationship = 'lifecycleLogs';

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        $query = $ownerRecord->lifecycleLogs();
        $user = auth()->user();

        if ($user === null) {
            return '0';
        }

        if ($user->hasRole('super_admin')) {
            return (string) $query->count();
        } elseif ($user->hasRole('manager')) {
            $query->where('wo_dep_id', $user->user_dep_id);
        }

        return (string) $query->count();
    }

    public function form(Schema $schema): Schema
    {
        return $schema
            ->components([
                Select::make('action_id')
                    ->relationship('action', 'a_id')
                    ->required(),
                Select::make('status_id')
                    ->relationship('status', 'status_id')
                    ->required(),
                TextInput::make('deploy_to_loc_id')
                    ->numeric()
                    ->default(null),
                Select::make('allocate_to_equipment_id')
                    ->relationship('allocateToEquipment', 'eqm_id')
                    ->default(null),
                Textarea::make('remarks')
                    ->default(null)
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
                Section::make('Lifecycle Information')
                    ->inlineLabel()
                    ->schema([
                        TextEntry::make('action.a_present_tense')
                            ->label('Action')
                            ->badge()
                            ->color(fn ($record) => match ($record->action_id) {
                                'dep', 'alc' => 'success',
                                'sidle' => 'danger',
                                'smt' => 'warning',
                                default => 'gray',
                            })
                            ->icon(fn ($record) => $record->action?->a_icon),
                        TextEntry::make('status.status_title')
                            ->label('Status')
                            ->badge()
                            ->color(fn ($record) => $record->status->status_color)
                            ->icon(fn ($record) => $record->status->status_icon),
                        TextEntry::make('deployToLocation.name')
                            ->label('Location')
                            ->visible(fn (): bool => $this->ownerHasAssetType(1))
                            ->placeholder('—'),
                        TextEntry::make('allocateToEquipment.eqm_name')
                            ->label('Allocated To Equipment')
                            ->visible(fn (): bool => $this->ownerHasAssetType(2))
                            ->placeholder('—'),
                        TextEntry::make('remarks')
                            ->label('Remarks')
                            ->placeholder('No remarks')
                            ->columnSpanFull(),
                        TextEntry::make('performedBy')
                            ->label('Performed By')
                            ->formatStateUsing(function ($record) {
                                if (! $record->performedBy) {
                                    return '—';
                                }

                                return trim(implode(' ', array_filter([$record->performedBy->user_fname, $record->performedBy->user_mname, $record->performedBy->user_lname])));
                            }),
                        TextEntry::make('logged_at')
                            ->label('Logged At')
                            ->dateTime('M d, Y h:i A'),
                    ]),
            ])->columns(1);
    }

    protected function ownerHasAssetType(int $assetTypeId): bool
    {
        return (int) $this->getOwnerRecord()->asset_type_id === $assetTypeId;
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('id')
            ->defaultSort('logged_at', 'desc')
            ->columns([
                TextColumn::make('action.a_past_tense')
                    ->label('Action')
                    ->badge()
                    ->color(fn ($record) => match ($record->action_id) {
                        'dep', 'alc' => 'success',
                        'sidle' => 'danger',
                        'smt' => 'warning',
                        default => 'gray',
                    })
                    ->icon(fn ($record) => $record->action?->a_icon)
                    ->searchable(),

                TextColumn::make('status.status_title')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => $record->status?->status_color)
                    ->searchable(),

                TextColumn::make('deployToLocation.name')
                    ->label('Location')
                    ->placeholder('—')
                    ->visible(fn (): bool => $this->ownerHasAssetType(1))
                    ->searchable(),

                TextColumn::make('allocateToEquipment.eqm_name')
                    ->label('Allocated To')
                    ->placeholder('—')
                    ->visible(fn (): bool => $this->ownerHasAssetType(2))
                    ->searchable(),

                TextColumn::make('remarks')
                    ->label('Remarks')
                    ->limit(40)
                    ->tooltip(fn ($record) => $record->remarks)
                    ->placeholder('—'),

                TextColumn::make('performedBy.user_fname')
                    ->label('Performed By')
                    ->formatStateUsing(function ($state, $record) {
                        if (! $record->performedBy) {
                            return '—';
                        }

                        return trim(implode(' ', array_filter([
                            $record->performedBy->user_fname,
                            $record->performedBy->user_mname,
                            $record->performedBy->user_lname,
                        ])));
                    })
                    ->searchable([
                        'user_fname',
                        'user_mname',
                        'user_lname',
                    ]),

                TextColumn::make('logged_at')
                    ->label('Date & Time')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])

            ->filters([
                // Add lifecycle filters later if needed.
            ])

            ->headerActions([
                // No CreateAction.
                // Lifecycle logs should normally be generated
                // by lifecycle actions, not manually created.
            ])

            ->recordActions([
                // Keep history read-only.
                ViewAction::make()
                    ->modalWidth('xl'),
            ])

            ->toolbarActions([
                BulkActionGroup::make([
                    // No delete actions for audit/history records.
                ]),
            ]);
    }
}
