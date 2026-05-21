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
        Schema::table('equipment_tasks_schedules', function (Blueprint $table) {
            $table->date('ets_due_effectivity_dt')->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_tasks_schedules', function (Blueprint $table) {
            $table->dateTime('ets_due_effectivity_dt')->change();
        });
    }
};
