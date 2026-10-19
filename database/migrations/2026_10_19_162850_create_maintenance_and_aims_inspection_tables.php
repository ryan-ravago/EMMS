<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Closed AppSheet inspections, kept as plain-text snapshots: department, equipment, checklist and people are
     * stored as text, so history survives those records being renamed or deleted. inspection_no is the natural
     * key used to avoid importing the same inspection twice. Child rows only reference their own parent.
     */
    public function up(): void
    {
        Schema::create('maintenance_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_no')->unique();
            $table->string('status')->index();
            $table->string('department_code', 20)->nullable()->index();
            $table->string('department_name')->nullable();
            $table->string('equipment_prc_code')->nullable()->index();
            $table->string('equipment_name')->nullable();
            $table->string('checklist_name')->nullable();
            $table->string('requested_by')->nullable();
            $table->dateTime('requested_at')->nullable()->index();
            $table->text('asset_admin_manager_note')->nullable();
            $table->string('manager_action')->nullable();
            $table->string('manager_action_by')->nullable();
            $table->text('manager_remarks')->nullable();
            $table->text('technicians')->nullable();
            $table->text('verifiers')->nullable();
            $table->string('tech_action')->nullable();
            $table->string('tech_action_by')->nullable();
            $table->text('tech_remarks')->nullable();
            $table->string('verifier_action')->nullable();
            $table->string('verifier_action_by')->nullable();
            $table->text('verifier_remarks')->nullable();
            $table->text('cancel_remarks')->nullable();
            $table->dateTime('closed_at')->nullable()->index();
        });

        Schema::create('aims_inspections', function (Blueprint $table) {
            $table->id();
            $table->string('inspection_no')->unique();
            $table->string('status')->index();
            $table->string('equipment_prc_code')->nullable()->index();
            $table->string('equipment_name')->nullable();
            $table->string('checklist_name')->nullable();
            $table->string('lessee')->nullable();
            $table->string('jobsite')->nullable();
            $table->string('operator')->nullable();
            $table->unsignedSmallInteger('day_duration')->nullable();
            $table->string('inspected_by')->nullable();
            $table->dateTime('inspected_at')->nullable();
            $table->text('remarks')->nullable();
            $table->string('requested_by')->nullable();
            $table->dateTime('requested_at')->nullable()->index();
            $table->text('completion_remarks')->nullable();
            $table->text('cancel_remarks')->nullable();
            $table->dateTime('closed_at')->nullable()->index();
        });

        foreach (['maintenance_inspection' => 'maintenance_inspections', 'aims_inspection' => 'aims_inspections'] as $singular => $parent) {
            Schema::create("{$singular}_items", function (Blueprint $table) use ($singular, $parent) {
                $table->id();
                $table->foreignId("{$singular}_id")->constrained($parent)->cascadeOnDelete();
                $table->string('task')->nullable();
                $table->string('result')->nullable();
                $table->text('remarks')->nullable();
            });

            Schema::create("{$singular}_logs", function (Blueprint $table) use ($singular, $parent) {
                $table->id();
                $table->foreignId("{$singular}_id")->constrained($parent)->cascadeOnDelete();
                $table->string('action')->nullable();
                $table->string('status')->nullable();
                $table->text('remarks')->nullable();
                $table->string('performed_by')->nullable();
                $table->dateTime('logged_at')->nullable();
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('aims_inspection_logs');
        Schema::dropIfExists('aims_inspection_items');
        Schema::dropIfExists('maintenance_inspection_logs');
        Schema::dropIfExists('maintenance_inspection_items');
        Schema::dropIfExists('aims_inspections');
        Schema::dropIfExists('maintenance_inspections');
    }
};
