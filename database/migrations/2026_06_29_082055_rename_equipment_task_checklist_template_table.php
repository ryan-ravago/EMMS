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
        Schema::rename(
            'equipment_task_checklist_template',
            'equipment_type_task_checklist_template'
        );
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::rename(
            'equipment_type_task_checklist_template',
            'equipment_task_checklist_template'
        );
    }
};
