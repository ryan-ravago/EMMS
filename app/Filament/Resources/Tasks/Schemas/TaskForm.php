<?php

namespace App\Filament\Resources\Tasks\Schemas;

use App\Models\Task;
use App\Models\TaskUsageType;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Illuminate\Validation\Rules\Unique;

class TaskForm
{
    public static function configure(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make('Task Details')
                    ->icon('heroicon-o-clipboard-document-list')
                    ->schema([
                        TextInput::make('task_name')
                            ->label('Task Name')
                            ->placeholder('Enter task name')
                            ->required()
                            ->columnSpanFull()
                            ->unique(
                                table: Task::class,
                                column: 'task_name',
                                modifyRuleUsing: fn (Unique $rule) => $rule->where('task_dep_id', auth()->user()->user_dep_id),
                                ignoreRecord: true,
                            )->validationMessages([
                                'unique' => 'The task name has already been taken.',
                            ]),
                        Select::make('task_tut_id')
                            ->label('Usage Type')
                            ->placeholder('Select a usage type')
                            ->relationship('taskUsageType', 'tut_name')
                            ->searchable()
                            ->preload()
                            ->required(fn () => ! in_array(auth()->user()?->department?->dep_code, ['MECH', 'ELEC']))
                            ->native(false)
                            ->exists(
                                table: TaskUsageType::class,
                                column: 'tut_id',
                            )
                            ->visible(fn () => ! in_array(auth()->user()?->department?->dep_code, ['MECH', 'ELEC'])),
                    ]),
            ]);
    }
}
