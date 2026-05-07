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
        Schema::create('equipment_checklist_assignments', function (Blueprint $table) {
            $table->id('eca_id');
            $table->foreignId('eca_clt_id')->references('clt_id')->on('checklist_templates')->restrictOnDelete();
            $table->foreignId('eca_eqm_id')->references('eqm_id')->on('equipment_units')->restrictOnDelete();
            $table->foreignId('eca_assigned_by')->references('user_id')->on('app_users')->restrictOnDelete();
            $table->dateTime('eca_assigned_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('equipment_checklist_assignments');
    }
};
