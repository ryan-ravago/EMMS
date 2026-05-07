<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Filament\Resources\ChecklistTemplates\ChecklistTemplateResource;
use App\Models\ChecklistTemplate;
use Filament\Actions\AttachAction;
use Filament\Actions\CreateAction;
use Filament\Actions\DetachAction;
use Filament\Actions\DetachBulkAction;
use Filament\Forms\Components\DateTimePicker;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;

class ChecklistTemplatesRelationManager extends RelationManager
{
    protected static string $relationship = 'checklistTemplates';

    protected static ?string $relatedResource = ChecklistTemplateResource::class;

    public function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('clt_name')
                    ->label('Name')
                    ->searchable(),
                TextColumn::make('checklistUsageType.cut_name')
                    ->label('Usage Type')
                    ->sortable(),
                TextColumn::make('department.dep_name')
                    ->label('Department')
                    ->sortable(),
                TextColumn::make('clt_interval_years')
                    ->label('Interval')
                    ->formatStateUsing(function ($record) {
                        $parts = array_filter([
                            $record->clt_interval_years  ? "{$record->clt_interval_years}y"  : null,
                            $record->clt_interval_months ? "{$record->clt_interval_months}mo" : null,
                            $record->clt_interval_weeks  ? "{$record->clt_interval_weeks}w"  : null,
                            $record->clt_interval_days   ? "{$record->clt_interval_days}d"   : null,
                        ]);

                        $interval = $parts ? implode(' ', $parts) : '—';
                        $time = $record->clt_schedule_time
                            ? \Carbon\Carbon::parse($record->clt_schedule_time)->format('h:i A')
                            : null;

                        return $time ? "{$interval} @ {$time}" : $interval;
                    })
            ])
            ->headerActions([
                CreateAction::make(),
                AttachAction::make()
                    ->recordSelectSearchColumns(['clt_name'])
                    ->recordTitleAttribute('clt_name')
                    ->preloadRecordSelect()
                    ->schema(fn(AttachAction $action) => [
                        $action->getRecordSelect()
                            ->live(),
                        DateTimePicker::make('eca_due_effectivity_dt')
                            ->label('Effectivity Date')
                            ->seconds(false)
                            ->required()
                            ->hidden(function (Get $get) {
                                $templateId = $get('recordId');
                                $template = ChecklistTemplate::find($templateId);
                                return !$template || $template->clt_cut_id != 2;
                            }),
                    ]),
            ])
            ->recordActions([
                DetachAction::make(),
            ])
            ->toolbarActions([
                DetachBulkAction::make(),
            ]);
    }
}
