<?php

namespace App\Filament\Resources\Equipment;

use App\Filament\Resources\Equipment\Pages\CreateEquipment;
use App\Filament\Resources\Equipment\Pages\EditEquipment;
use App\Filament\Resources\Equipment\Pages\ListEquipment;
use App\Filament\Resources\Equipment\Pages\ViewEquipment;
use App\Filament\Resources\Equipment\RelationManagers\AccessoriesRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\AimsInspectionsRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\EditLogsRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\EquipmentTaskChecklistTemplatesRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\EquipmentTasksSchedulesRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\LifecycleLogsRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\MaintenanceInspectionsRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\MaintenanceTasksRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\RequestorWorkOrdersRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\TagLogsRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\TagsRelationManager;
use App\Filament\Resources\Equipment\RelationManagers\WorkOrdersRelationManager;
use App\Filament\Resources\Equipment\Schemas\EquipmentForm;
use App\Filament\Resources\Equipment\Schemas\EquipmentInfolist;
use App\Filament\Resources\Equipment\Tables\EquipmentTable;
use App\Models\AppUser;
use App\Models\AssetType;
use App\Models\Equipment;
use BackedEnum;
use Closure;
use Filament\Panel;
use Filament\Resources\Resource;
use Filament\Resources\ResourceConfiguration;
use Filament\Schemas\Schema;
use Filament\Support\Icons\Heroicon;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Auth;

class EquipmentResource extends Resource
{
    protected static ?string $model = Equipment::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCube;

    protected static ?string $configurationClass = ResourceConfiguration::class;

    protected static ?string $recordTitleAttribute = 'eqm_name';

    protected static ?string $navigationLabel = 'Equipment';

    protected static ?string $modelLabel = 'Equipment';

    protected static ?string $pluralModelLabel = 'Equipment';

    protected static ?string $slug = 'asset';

    protected static ?int $navigationSort = 2;

    /**
     * The asset pages. Each is one configuration of this resource with its own Shield permission
     * (the policy ability). "Assets" lists every asset; the others list one asset type (matched by code).
     *
     * @var array<string, array{type: ?string, ability: string, label: string, plural: string}>
     */
    public const PAGES = [
        'assets' => [
            'type' => null,
            'ability' => 'viewAssetList',
            'label' => 'Asset',
            'plural' => 'Assets',
        ],
        'equipment' => [
            'type' => AssetType::EQUIPMENT,
            'ability' => 'viewEquipmentList',
            'label' => 'Equipment',
            'plural' => 'Equipment',
        ],
        'accessories' => [
            'type' => AssetType::ACCESSORY,
            'ability' => 'viewAccessoryList',
            'label' => 'Accessory',
            'plural' => 'Accessories',
        ],
        'service-vehicles' => [
            'type' => AssetType::SERVICE_VEHICLE,
            'ability' => 'viewServiceVehicleList',
            'label' => 'Service Vehicle',
            'plural' => 'Service Vehicles',
        ],
    ];

    /**
     * The panel menu items for the asset pages are added in the panel provider, so the
     * resource does not register a menu item of its own.
     */
    public static function shouldRegisterNavigation(): bool
    {
        return false;
    }

    /**
     * The "Assets" page that listed every type no longer exists: only the pages in PAGES are routed.
     */
    public static function registerRoutes(Panel $panel, ?Closure $registerPageRoutes = null, ?ResourceConfiguration $configuration = null): void
    {
        if ($configuration === null && $registerPageRoutes === null) {
            return;
        }

        parent::registerRoutes($panel, $registerPageRoutes, $configuration);
    }

    public static function currentPageKey(): ?string
    {
        $key = static::getConfiguration()?->getKey();

        return isset(self::PAGES[$key]) ? $key : null;
    }

    public static function currentAssetTypeCode(): ?string
    {
        $key = static::currentPageKey();

        return $key === null ? null : self::PAGES[$key]['type'];
    }

    /**
     * Whether the signed-in user may open one of the asset pages (a Shield permission per page).
     */
    public static function canViewPage(string $key): bool
    {
        $user = Auth::user();

        return $user !== null
            && isset(self::PAGES[$key])
            && $user->can(self::PAGES[$key]['ability'], Equipment::class);
    }

    public static function canViewAny(): bool
    {
        $key = static::currentPageKey();

        return $key === null ? parent::canViewAny() : static::canViewPage($key);
    }

    /**
     * The page a record belongs to, from its asset type.
     */
    public static function pageKeyForRecord(mixed $record): ?string
    {
        if (! $record instanceof Equipment) {
            $record = is_scalar($record) ? Equipment::query()->withoutEagerLoads()->find($record, ['eqm_id', 'asset_type_id']) : null;
        }

        $code = AssetType::codeFor($record?->asset_type_id === null ? null : (int) $record->asset_type_id);

        foreach (self::PAGES as $key => $page) {
            if ($code !== null && $page['type'] === $code) {
                return $key;
            }
        }

        return null;
    }

    /**
     * There is no page without a configuration. On the Assets page links stay there; anywhere else a link
     * goes to the page of the record's asset type, or to the Assets page (untyped assets, lists).
     */
    public static function getUrl(?string $name = null, array $parameters = [], bool $isAbsolute = true, ?string $panel = null, ?Model $tenant = null, bool $shouldGuessMissingParameters = false, ?string $configuration = null): string
    {
        if (blank($configuration)) {
            $current = static::currentPageKey();
            $recordPage = $current !== 'assets' && array_key_exists('record', $parameters)
                ? static::pageKeyForRecord($parameters['record'])
                : null;

            $configuration = $recordPage
                ?? $current
                ?? (array_key_exists('record', $parameters) || static::canViewPage('assets') ? 'assets' : 'equipment');

            // Already inside that page's configuration: nothing to switch (and switching again would loop).
            if ($configuration === static::currentPageKey()) {
                $configuration = null;
            }
        }

        return parent::getUrl($name, $parameters, $isAbsolute, $panel, $tenant, $shouldGuessMissingParameters, $configuration);
    }

    public static function getNavigationBadge(): ?string
    {
        $query = static::getModel()::query();

        if ($type = static::currentAssetTypeCode()) {
            $query->ofAssetType($type);
        }

        return (string) $query->count();
    }

    public static function getNavigationLabel(): string
    {
        return self::PAGES[static::currentPageKey()]['plural'] ?? 'Assets';
    }

    public static function getModelLabel(): string
    {
        return self::PAGES[static::currentPageKey()]['label'] ?? 'Asset';
    }

    public static function getPluralModelLabel(): string
    {
        return self::PAGES[static::currentPageKey()]['plural'] ?? 'Assets';
    }

    public static function form(Schema $schema): Schema
    {
        return EquipmentForm::configure($schema);
    }

    public static function infolist(Schema $schema): Schema
    {
        return EquipmentInfolist::configure($schema);
    }

    public static function getEloquentQuery(): Builder
    {
        $query = parent::getEloquentQuery()
            ->with(['equipmentModel', 'type', 'brand', 'assetType', 'parent.location', 'location.parent', 'lifecycleStatus']);

        if ($type = static::currentAssetTypeCode()) {
            $query->ofAssetType($type);
        }

        return $query;
    }

    public static function table(Table $table): Table
    {
        return EquipmentTable::configure($table);
    }

    public static function getRelations(): array
    {
        $relations = [
            AccessoriesRelationManager::class,
            TagsRelationManager::class,
            TagLogsRelationManager::class,
            LifecycleLogsRelationManager::class,
            WorkOrdersRelationManager::class,
            MaintenanceTasksRelationManager::class,
            MaintenanceInspectionsRelationManager::class,
            AimsInspectionsRelationManager::class,
            EditLogsRelationManager::class,
        ];

        // /** @var AppUser|null $user */
        // $user = Auth::user();

        // // Standard maintenance tabs for maintenance roles
        // if ($user?->hasAnyRole(['super_admin', 'manager', 'technician'])) {
        //     $relations = array_merge($relations, [
        //         WorkOrdersRelationManager::class,
        //         // EquipmentTaskChecklistTemplatesRelationManager::class,
        //         // EquipmentTasksSchedulesRelationManager::class,
        //         MaintenanceTasksRelationManager::class,
        //     ]);
        // }

        // // Requestor specific tab
        // if ($user instanceof AppUser && $user->hasRole('requestor')) {
        //     $relations[] = RequestorWorkOrdersRelationManager::class;
        // }

        return $relations;
    }

    public static function getPages(): array
    {
        return [
            'index' => ListEquipment::route('/'),
            'create' => CreateEquipment::route('/create'),
            'view' => ViewEquipment::route('/{record}'),
            'edit' => EditEquipment::route('/{record}/edit'),
        ];
    }
}
