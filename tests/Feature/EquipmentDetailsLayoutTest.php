<?php

namespace Tests\Feature;

use App\Filament\Resources\Equipment\Schemas\EquipmentInfolist;
use App\Models\AssetType;
use App\Models\Equipment;
use Filament\Infolists\Components\Entry;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema as DatabaseSchema;
use Livewire\Component;
use Tests\TestCase;

class EquipmentDetailsLayoutTest extends TestCase
{
    /**
     * Every field the Details tab showed before the layout was reorganised. None of them may be lost.
     */
    private const FIELDS = [
        'eqm_prc_code', 'eqm_name', 'eqm_is_active',
        'eqm_pm_itrv_type', 'eqm_pm_itrv_value', 'eqm_pm_itrv_start_date', 'eqm_next_pm_due_at', 'eqm_last_pm_notified_at',
        'assetType.name', 'parent.eqm_name', 'lifecycleStatus.status_title', 'categories', 'location.full_path',
        'equipmentModel.eqmm_name', 'type.eqmt_name', 'brand.eqmb_name', 'eqm_plate_num', 'eqm_chassis_no',
        'eqm_date_purchased', 'year_model', 'specifications',
    ];

    protected function setUp(): void
    {
        parent::setUp();

        DatabaseSchema::create('asset_types', function (Blueprint $table): void {
            $table->id();
            $table->string('code', 10)->unique();
            $table->string('name')->unique();
        });

        AssetType::flushIdCache();
    }

    private function schema(): Schema
    {
        $livewire = new class extends Component implements HasSchemas
        {
            use InteractsWithSchemas;

            public function render(): string
            {
                return '';
            }
        };

        return EquipmentInfolist::configure(Schema::make($livewire)->record(new Equipment));
    }

    /**
     * @return list<string>
     */
    private function entryNames(): array
    {
        return collect($this->schema()->getFlatComponents(withHidden: true))
            ->filter(fn ($component): bool => $component instanceof Entry)
            ->map(fn (Entry $entry): string => $entry->getName())
            ->values()
            ->all();
    }

    public function test_the_details_tab_has_one_asset_details_section_then_the_schedule_and_specifications(): void
    {
        $headings = collect($this->schema()->getFlatComponents(withHidden: true))
            ->filter(fn ($component): bool => $component instanceof Section)
            ->map(fn (Section $section) => (string) $section->getHeading())
            ->values()
            ->all();

        $this->assertSame(['Asset Details', 'Preventive Maintenance Schedule', 'Specifications'], $headings);
    }

    public function test_asset_details_lists_identity_then_classification_then_specifics_with_inline_labels(): void
    {
        $details = collect($this->schema()->getFlatComponents(withHidden: true))
            ->first(fn ($component): bool => $component instanceof Section && (string) $component->getHeading() === 'Asset Details');

        $this->assertTrue($details->hasInlineLabel());
        $this->assertSame(
            [
                'eqm_name', 'eqm_prc_code', 'eqm_is_active',
                'assetType.name', 'lifecycleStatus.status_title', 'location.full_path', 'parent.eqm_name',
                'brand.eqmb_name', 'equipmentModel.eqmm_name', 'type.eqmt_name', 'year_model', 'eqm_plate_num', 'eqm_chassis_no', 'eqm_date_purchased',
                'categories',
            ],
            collect($details->getDefaultChildComponents())
                ->filter(fn ($component): bool => $component instanceof Entry)
                ->map(fn (Entry $entry): string => $entry->getName())
                ->values()
                ->all(),
        );
    }

    public function test_no_field_was_lost_in_the_new_layout(): void
    {
        $shown = $this->entryNames();

        foreach (self::FIELDS as $field) {
            $this->assertContains($field, $shown, "{$field} is missing from the Details tab");
        }
    }

    public function test_no_field_is_shown_twice(): void
    {
        $names = $this->entryNames();

        $this->assertSame($names, array_values(array_unique($names)));
    }

    public function test_the_overview_comes_first_with_the_asset_name_and_code(): void
    {
        $this->assertSame(['eqm_name', 'eqm_prc_code', 'eqm_is_active'], array_slice($this->entryNames(), 0, 3));
    }
}
