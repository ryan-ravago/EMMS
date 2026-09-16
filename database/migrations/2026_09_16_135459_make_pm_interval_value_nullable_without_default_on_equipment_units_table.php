<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->unsignedSmallInteger('eqm_pm_itrv_value')
                ->nullable()
                ->default(null)
                ->change();
        });

        DB::table('equipment_units')
            ->whereNull('eqm_pm_itrv_type')
            ->update([
                'eqm_pm_itrv_value' => null,
                'eqm_next_pm_due_at' => null,
            ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->unsignedSmallInteger('eqm_pm_itrv_value')
                ->default(1)
                ->change();
        });
    }
};
