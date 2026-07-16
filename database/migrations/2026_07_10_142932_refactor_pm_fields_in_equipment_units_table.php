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
            // Drop old columns
            $table->dropColumn([
                'eqm_pm_itrv_years',
                'eqm_pm_itrv_months',
                'eqm_pm_itrv_weeks',
                'eqm_pm_itrv_days',
            ]);

            // Add new columns
            $table->enum('eqm_pm_itrv_type', ['monthly', 'weekly'])->nullable()->after('eqm_name');
            $table->unsignedSmallInteger('eqm_pm_itrv_value')->default(1)->after('eqm_pm_itrv_type');

            // Change datetime to date
            $table->dropColumn('eqm_pm_itrv_start_date');
            $table->date('eqm_pm_itrv_start_date')->nullable()->after('eqm_pm_itrv_value');

            $table->dropColumn('eqm_next_pm_due_at');
            $table->date('eqm_next_pm_due_at')->nullable();

            $table->dropColumn('eqm_last_pm_notified_at');
            $table->date('eqm_last_pm_notified_at')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->dropColumn([
                'eqm_pm_itrv_type',
                'eqm_pm_itrv_value',
                'eqm_pm_itrv_start_date',
                'eqm_next_pm_due_at',
                'eqm_last_pm_notified_at',
            ]);
        });
    }
};
