<?php

namespace App\Filament\Widgets;

use App\Filament\Resources\AssetMovements\AssetMovementResource;
use App\Models\AssetMovement;
use Carbon\CarbonInterface;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Filament\Widgets\TableWidget as BaseWidget;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\HtmlString;

/**
 * Super admin, asset admin and asset manager: equipment that left and has not been scanned back in.
 */
class EquipmentOutNowWidget extends BaseWidget
{
    protected static ?int $sort = 8;

    protected int|string|array $columnSpan = 'full';

    public function getHeading(): string|HtmlString|null
    {
        return new HtmlString('<a href="'.e(AssetMovementResource::getUrl('index', ['tab' => 'missing_in'])).'" class="hover:underline transition">Equipment Currently Out</a>');
    }

    public static function canView(): bool
    {
        $user = Auth::user();

        return $user !== null
            && $user->hasAnyRole(['super_admin', 'asset_admin', 'asset_manager'])
            && $user->can('viewAny', AssetMovement::class);
    }

    public function table(Table $table): Table
    {
        return $table
            ->query(fn () => AssetMovement::query()->with('asset')->open()->latest('time_out')->limit(8))
            ->columns([
                TextColumn::make('asset.eqm_prc_code')
                    ->label('Equipment')
                    ->description(fn (AssetMovement $record): ?string => $record->asset?->eqm_name),
                TextColumn::make('time_out')
                    ->label('Left')
                    ->dateTime('M d, Y h:i A')
                    ->placeholder('Missing'),
                TextColumn::make('away')
                    ->label('Out For')
                    ->state(fn (AssetMovement $record): ?string => $record->time_out?->diffForHumans(null, ['syntax' => CarbonInterface::DIFF_ABSOLUTE, 'parts' => 2])),
            ])
            ->emptyStateHeading('Nothing is out')
            ->emptyStateDescription('Every equipment that left has been scanned back in.')
            ->recordUrl(fn (AssetMovement $record): string => AssetMovementResource::getUrl('view', ['record' => $record]))
            ->paginated(false)
            ->poll('60s');
    }
}
