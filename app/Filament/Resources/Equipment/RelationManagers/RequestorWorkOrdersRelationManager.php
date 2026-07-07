<?php

namespace App\Filament\Resources\Equipment\RelationManagers;

use App\Filament\Resources\RequestorWorkOrders\RequestorWorkOrderResource;
use App\Mail\WorkOrderActionRequiredMail;
use App\Mail\WorkOrderConfirmationMail;
use App\Models\AppUser;
use App\Models\Department;
use App\Models\Priority;
use App\Models\RequestorWorkOrder;
use App\Models\WorkOrderLog;
use Filament\Actions\CreateAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Wizard;
use Filament\Schemas\Components\Wizard\Step;
use Filament\Support\Enums\Width;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class RequestorWorkOrdersRelationManager extends RelationManager
{
    protected static string $relationship = 'workOrders';

    protected static ?string $title = 'My Work Order';

    protected static ?string $relatedResource = RequestorWorkOrderResource::class;

    public static function canViewForRecord(Model $ownerRecord, string $pageClass): bool
    {
        return auth()->user()?->hasRole('requestor') ?? false;
    }

    public static function getBadge(Model $ownerRecord, string $pageClass): ?string
    {
        return (string) $ownerRecord->workOrders()
            ->where('wo_created_by', Auth::id())
            ->count();
    }

    public function table(Table $table): Table
    {
        return $table
            ->headerActions([
                CreateAction::make()
                    ->visible(fn (): bool => (bool) $this->getOwnerRecord()->eqm_is_active)
                    ->authorize(fn () => Auth::user()->can('create', RequestorWorkOrder::class))
                    ->color('primary')
                    ->closeModalByClickingAway(false)
                    ->modalCloseButton(false)
                    ->modalWidth(Width::SevenExtraLarge)
                    ->schema([
                        Wizard::make([
                            Step::make('Work Order Form')
                                ->icon('heroicon-o-wrench-screwdriver')
                                ->schema([
                                    Section::make('Equipment')
                                        ->icon('heroicon-o-truck')
                                        ->columns(2)
                                        ->columnSpanFull()
                                        ->schema([
                                            Select::make('wo_eqm_id')
                                                ->label('Equipment')
                                                ->options([
                                                    $this->getOwnerRecord()->getKey() => $this->getOwnerRecord()->eqm_name,
                                                ])
                                                ->default($this->getOwnerRecord()->getKey())
                                                ->disabled()
                                                ->dehydrated()
                                                ->required()
                                                ->native(false),
                                            Select::make('wo_dep_id')
                                                ->label('Department')
                                                ->options(
                                                    Department::where('is_maintenance', 1)
                                                        ->pluck('dep_name', 'dep_id')
                                                )
                                                ->searchable()
                                                ->preload()
                                                ->required()
                                                ->native(false),
                                        ]),

                                    Section::make('Details')
                                        ->icon('heroicon-o-document-text')
                                        ->columns(2)
                                        ->columnSpanFull()
                                        ->schema([
                                            Textarea::make('wo_title')
                                                ->label('Title')
                                                ->required()
                                                ->columnSpanFull(),
                                            Textarea::make('wo_req_desc')
                                                ->label('Description')
                                                ->required()
                                                ->columnSpanFull(),
                                            Select::make('wo_prio_id')
                                                ->label('Priority')
                                                ->options(Priority::pluck('prio_name', 'prio_id'))
                                                ->searchable()
                                                ->required()
                                                ->native(false),
                                            FileUpload::make('wo_attachments')
                                                ->label('Attachments')
                                                ->multiple()
                                                ->nullable()
                                                ->columnSpanFull()
                                                ->maxFiles(10)
                                                ->maxParallelUploads(5)
                                                ->panelLayout('grid')
                                                ->reorderable()
                                                ->appendFiles()
                                                ->openable()
                                                ->downloadable()
                                                ->previewable()
                                                ->maxSize(10240)
                                                ->imageEditor(),
                                        ]),
                                ]),
                        ])
                            ->columnSpanFull(),
                    ])
                    ->mutateFormDataUsing(function (array $data): array {
                        $now = now();
                        $depCode = DB::table('departments')
                            ->where('dep_id', $data['wo_dep_id'])
                            ->value('dep_code');

                        $count = DB::table('work_orders')
                            ->where('wo_dep_id', $data['wo_dep_id'])
                            ->whereDate('wo_created_dt', $now->toDateString())
                            ->count() + 1;

                        $data['wo_no'] = 'WO-'.$depCode.'-'.$now->format('ymd').str_pad($count, 3, '0', STR_PAD_LEFT);
                        $data['wo_status_id'] = 'pndwor';
                        $data['wo_created_by'] = Auth::id();
                        $data['wo_created_dt'] = $now;

                        return $data;
                    })
                    ->after(function (Model $record) {
                        WorkOrderLog::create([
                            'wol_wo_id' => $record->wo_id,
                            'wol_a_id' => 'create',
                            'wol_status_id' => 'pndwor',
                            'wol_a_log' => DB::table('actions')->where('a_id', 'create')->value('a_past_tense'),
                            'wol_status_log' => DB::table('statuses')->where('status_id', 'pndwor')->value('status_title'),
                            'wol_by' => Auth::id(),
                            'wol_dt' => now(),
                        ]);

                        // 1. Send confirmation email to the requestor
                        if ($record->createdBy && $record->createdBy->user_email) {
                            Mail::to($record->createdBy->user_email)
                                ->queue(new WorkOrderConfirmationMail($record));
                        }

                        // 2. Notify managers of the assigned department
                        $managers = AppUser::whereHas('roles', fn ($q) => $q->where('name', 'manager'))
                            ->where('user_dep_id', $record->wo_dep_id)
                            ->get();

                        foreach ($managers as $manager) {
                            if ($manager->user_email) {
                                Mail::to($manager->user_email)
                                    ->queue(new WorkOrderActionRequiredMail($record, $manager));
                            }
                        }
                    }),
            ])
            ->columns([
                TextColumn::make('wo_no')
                    ->label('WO No.')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('wo_title')
                    ->label('Title')
                    ->searchable()
                    ->wrap(),
                TextColumn::make('priority.prio_name')
                    ->label('Priority')
                    ->badge()
                    ->sortable(),
                TextColumn::make('status.status_title')
                    ->label('Status')
                    ->badge()
                    ->color(fn ($record) => $record->status->status_color)
                    ->icon(fn ($record) => $record->status->status_icon)
                    ->sortable(),
                TextColumn::make('wo_created_dt')
                    ->label('Created At')
                    ->dateTime('M d, Y h:i A')
                    ->sortable(),
            ])
            ->modifyQueryUsing(fn (Builder $query) => $query->where('wo_created_by', Auth::id()))
            ->filters([
                SelectFilter::make('wo_status_id')
                    ->label('Status')
                    ->relationship('status', 'status_title')
                    ->searchable()
                    ->preload(),
                Filter::make('wo_created_dt')
                    ->label('Created At')
                    ->schema([
                        DatePicker::make('from')->label('From')->native(false),
                        DatePicker::make('until')->label('Until')->native(false),
                    ])
                    ->query(function (Builder $query, array $data) {
                        return $query
                            ->when($data['from'], fn ($q) => $q->whereDate('wo_created_dt', '>=', $data['from']))
                            ->when($data['until'], fn ($q) => $q->whereDate('wo_created_dt', '<=', $data['until']));
                    }),
            ])
            ->recordActions([
                ViewAction::make()
                    ->url(fn (Model $record): string => RequestorWorkOrderResource::getUrl('view', ['record' => $record])),
                EditAction::make(),
            ]);
    }
}
