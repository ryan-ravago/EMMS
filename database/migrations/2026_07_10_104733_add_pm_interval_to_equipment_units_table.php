<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->unsignedSmallInteger('eqm_pm_itrv_years')->default(0)->after('eqm_name');
            $table->unsignedSmallInteger('eqm_pm_itrv_months')->default(0)->after('eqm_pm_itrv_years');
            $table->unsignedSmallInteger('eqm_pm_itrv_weeks')->default(0)->after('eqm_pm_itrv_months');
            $table->unsignedSmallInteger('eqm_pm_itrv_days')->default(0)->after('eqm_pm_itrv_weeks');
            $table->dateTime('eqm_pm_itrv_start_date')->nullable()->after('eqm_pm_itrv_days');
            $table->dateTime('eqm_next_pm_due_at')->nullable()->after('eqm_pm_itrv_start_date');
            $table->dateTime('eqm_last_pm_notified_at')->nullable()->after('eqm_next_pm_due_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->dropColumn([
                'eqm_pm_itrv_years',
                'eqm_pm_itrv_months',
                'eqm_pm_itrv_weeks',
                'eqm_pm_itrv_days',
                'eqm_pm_itrv_start_date',
                'eqm_next_pm_due_at',
                'eqm_last_pm_notified_at',
            ]);
        });
    }
};
