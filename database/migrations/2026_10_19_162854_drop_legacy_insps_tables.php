<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Removes the old insps / insp_items tables once their data has been moved into maintenance_inspections.
     * Run this separately, after checking the moved rows.
     */
    public function up(): void
    {
        Schema::dropIfExists('insp_items');
        Schema::dropIfExists('insps');
    }

    /**
     * Recreates the empty structure only; the data cannot be restored.
     */
    public function down(): void
    {
        Schema::create('insps', function (Blueprint $table) {
            $table->string('insp_id')->primary();
            $table->string('insp_no')->nullable();
            $table->unsignedBigInteger('insp_dep_id')->nullable()->index();
            $table->unsignedBigInteger('insp_eqm_id')->nullable()->index();
            $table->string('is_submitted')->nullable()->index();
            $table->string('checklist_template_name')->nullable();
            $table->text('checklist_temp_items')->nullable();
            $table->longText('insp_remarks')->nullable();
            $table->string('insp_by')->nullable();
            $table->string('insp_submitted_by')->nullable();
            $table->dateTime('insp_submitted_at')->nullable()->index();

            $table->foreign('insp_dep_id')->references('dep_id')->on('departments')->restrictOnDelete();
            $table->foreign('insp_eqm_id')->references('eqm_id')->on('equipment_units')->restrictOnDelete();
        });

        Schema::create('insp_items', function (Blueprint $table) {
            $table->string('inspi_id')->primary();
            $table->string('inspi_insp_id')->index();
            $table->foreign('inspi_insp_id')->references('insp_id')->on('insps')->cascadeOnDelete();
            $table->string('inspi_task')->nullable();
            $table->enum('inspi_result', ['Passed', 'Failed', 'N/A'])->nullable()->index();
            $table->longText('inspi_remarks')->nullable();
        });
    }
};
