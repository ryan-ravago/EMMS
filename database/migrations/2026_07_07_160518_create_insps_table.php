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
        Schema::create('insps', function (Blueprint $table) {
            $table->string('insp_id')->primary();
            $table->string('insp_no')->nullable();
            $table->string('insp_dep_id')->nullable()->index();
            $table->unsignedBigInteger('insp_eqm_id')->nullable()->index();
            $table->string('is_submitted')->nullable()->index();
            $table->string('checklist_template_name')->nullable();
            $table->text('checklist_temp_items')->nullable();
            $table->longText('insp_remarks')->nullable();
            $table->string('insp_by')->nullable();
            $table->string('insp_submitted_by')->nullable();
            $table->dateTime('insp_submitted_at')->nullable()->index();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insps');
    }
};
