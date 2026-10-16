<?php

namespace Tests\Feature;

use App\Filament\Resources\Equipment\Resources\WorkOrders\Schemas\WorkOrderForm as EquipmentWorkOrderForm;
use App\Filament\Resources\RequestorWorkOrders\Schemas\RequestorWorkOrderForm;
use App\Filament\Resources\WorkOrders\Schemas\WorkOrderForm;
use App\Models\WorkOrder;
use App\Models\WorkOrderType;
use Filament\Forms\Components\Select;
use Filament\Schemas\Concerns\InteractsWithSchemas;
use Filament\Schemas\Contracts\HasSchemas;
use Filament\Schemas\Schema as FilamentSchema;
use Illuminate\Database\QueryException;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Livewire\Component;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\TestCase;

class WorkOrderTypeTest extends TestCase
{
    protected function setUp(): void
    {
        parent::setUp();

        config(['activitylog.enabled' => false]);

        (require base_path('database/migrations/2026_10_15_155231_create_work_order_types_table.php'))->up();

        Schema::create('work_orders', function (Blueprint $table): void {
            $table->id('wo_id');
            $table->string('wo_no')->nullable();
        });

        (require base_path('database/migrations/2026_10_15_155233_add_wo_type_id_to_work_orders_table.php'))->up();
    }

    public function test_work_order_belongs_to_a_type_and_type_has_many_work_orders(): void
    {
        $repair = WorkOrderType::factory()->create(['code' => 'REP', 'type' => 'Repair']);

        $workOrder = WorkOrder::create(['wo_no' => 'WO-1', 'wo_type_id' => $repair->id]);

        $this->assertTrue($workOrder->type->is($repair));
        $this->assertSame('Repair', $workOrder->type->type);
        $this->assertTrue($repair->workOrders->first()->is($workOrder));
    }

    public function test_work_order_type_is_optional_so_existing_work_orders_stay_valid(): void
    {
        $workOrder = WorkOrder::create(['wo_no' => 'WO-2']);

        $this->assertNull($workOrder->wo_type_id);
        $this->assertNull($workOrder->type);
    }

    public function test_code_and_type_must_be_unique(): void
    {
        WorkOrderType::create(['code' => 'INS', 'type' => 'Inspection']);

        $this->expectException(QueryException::class);

        WorkOrderType::create(['code' => 'INS', 'type' => 'Another']);
    }

    public function test_type_name_must_be_unique(): void
    {
        WorkOrderType::create(['code' => 'INS', 'type' => 'Inspection']);

        $this->expectException(QueryException::class);

        WorkOrderType::create(['code' => 'INS2', 'type' => 'Inspection']);
    }

    public function test_a_type_in_use_cannot_be_deleted(): void
    {
        $type = WorkOrderType::create(['code' => 'INST', 'type' => 'Installation']);
        WorkOrder::create(['wo_no' => 'WO-3', 'wo_type_id' => $type->id]);

        $this->expectException(QueryException::class);

        $type->delete();
    }

    public function test_work_order_cannot_reference_a_missing_type(): void
    {
        $this->expectException(QueryException::class);

        WorkOrder::create(['wo_no' => 'WO-4', 'wo_type_id' => 999]);
    }

    /**
     * @return array<string, array{0: class-string}>
     */
    public static function workOrderForms(): array
    {
        return [
            'requestor form' => [RequestorWorkOrderForm::class],
            'work order form' => [WorkOrderForm::class],
            'equipment work order form' => [EquipmentWorkOrderForm::class],
        ];
    }

    #[DataProvider('workOrderForms')]
    public function test_work_order_forms_have_a_required_type_select(string $formClass): void
    {
        $livewire = new class extends Component implements HasSchemas
        {
            use InteractsWithSchemas;

            public function render(): string
            {
                return '';
            }
        };

        $schema = $formClass::configure(FilamentSchema::make($livewire));

        $field = collect($schema->getFlatComponents(withHidden: true))
            ->first(fn ($component) => $component instanceof Select && $component->getName() === 'wo_type_id');

        $this->assertNotNull($field, 'wo_type_id select is missing');
        $this->assertSame('Type', $field->getLabel());
        $this->assertSame('type', $field->getRelationshipName());
        $this->assertTrue($field->isRequired());
    }

    public function test_migration_can_be_rolled_back(): void
    {
        (require base_path('database/migrations/2026_10_15_155233_add_wo_type_id_to_work_orders_table.php'))->down();

        $this->assertFalse(Schema::hasColumn('work_orders', 'wo_type_id'));
    }
}
