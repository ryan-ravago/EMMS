<?php

namespace Tests\Unit;

use App\Filament\Resources\Brands\Pages\ViewBrand;
use App\Filament\Resources\Categories\Pages\ViewCategory;
use App\Filament\Resources\Departments\Pages\ViewDepartment;
use App\Filament\Resources\Equipment\Pages\ViewEquipment;
use App\Filament\Resources\Equipment\Resources\AssetTags\Pages\ViewAssetTag;
use App\Filament\Resources\Equipment\Resources\Insps\Pages\ViewInsps;
use App\Filament\Resources\Equipment\Resources\WorkOrders\Pages\ViewWorkOrder as ViewEquipmentWorkOrder;
use App\Filament\Resources\EquipmentTypes\Pages\ViewEquipmentType;
use App\Filament\Resources\InspectionItems\Pages\ViewInspectionItem;
use App\Filament\Resources\Insps\Pages\ViewInsp;
use App\Filament\Resources\Locations\Pages\ViewLocation;
use App\Filament\Resources\MaintenanceTasks\Pages\ViewMaintenanceTask;
use App\Filament\Resources\Models\Pages\ViewModel;
use App\Filament\Resources\RequestorWorkOrders\Pages\ViewRequestorWorkOrder;
use App\Filament\Resources\TechnicianWorkOrders\Pages\ViewTechnicianWorkOrder;
use App\Filament\Resources\WorkOrders\Pages\ViewWorkOrder;
use Filament\Resources\Pages\Enums\ContentTabPosition;
use PHPUnit\Framework\Attributes\DataProvider;
use PHPUnit\Framework\TestCase;

class ViewEquipmentDetailsTabTest extends TestCase
{
    /**
     * @return array<string, array{class-string}>
     */
    public static function pagesWithRelationManagers(): array
    {
        return [
            'equipment' => [ViewEquipment::class],
            'work orders' => [ViewWorkOrder::class],
            'requestor work orders' => [ViewRequestorWorkOrder::class],
            'technician work orders' => [ViewTechnicianWorkOrder::class],
            'equipment work orders' => [ViewEquipmentWorkOrder::class],
            'brands' => [ViewBrand::class],
            'categories' => [ViewCategory::class],
            'departments' => [ViewDepartment::class],
            'asset tags' => [ViewAssetTag::class],
            'equipment insps' => [ViewInsps::class],
            'equipment types' => [ViewEquipmentType::class],
            'inspection items' => [ViewInspectionItem::class],
            'insps' => [ViewInsp::class],
            'locations' => [ViewLocation::class],
            'maintenance tasks' => [ViewMaintenanceTask::class],
            'models' => [ViewModel::class],
        ];
    }

    /**
     * @param  class-string  $pageClass
     */
    #[DataProvider('pagesWithRelationManagers')]
    public function test_details_tab_is_combined_with_relation_managers_and_comes_first(string $pageClass): void
    {
        $page = new $pageClass;

        $this->assertTrue($page->hasCombinedRelationManagerTabsWithContent());
        $this->assertSame('Details', $page->getContentTabLabel());

        $this->assertSame(ContentTabPosition::Before, $page->getContentTabPosition());
    }
}
