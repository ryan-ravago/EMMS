<?php

namespace App\Filament\Concerns;

use BackedEnum;
use Filament\Resources\Pages\Enums\ContentTabPosition;
use Filament\Support\Icons\Heroicon;

/**
 * Shows the record's infolist as the first tab, ahead of the relation manager tabs,
 * instead of rendering it above them.
 */
trait HasDetailsTab
{
    public function hasCombinedRelationManagerTabsWithContent(): bool
    {
        return true;
    }

    public function getContentTabLabel(): ?string
    {
        return 'Details';
    }

    public function getContentTabIcon(): string|BackedEnum|null
    {
        return Heroicon::OutlinedInformationCircle;
    }

    public function getContentTabPosition(): ?ContentTabPosition
    {
        return ContentTabPosition::Before;
    }
}
