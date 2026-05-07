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
        Schema::create('checklist_templates', function (Blueprint $table) {
            $table->id('clt_id');
            $table->string('clt_name');
            $table->unsignedInteger('clt_cut_id')->constrained('checklist_usage_types', 'cut_id');
            $table->foreignId('clt_dep_id')->constrained('departments', 'dep_id');
            $table->integer('clt_interval_years')->nullable();
            $table->integer('clt_interval_months')->nullable();
            $table->integer('clt_interval_weeks')->nullable();
            $table->integer('clt_interval_days')->nullable();
            $table->time('clt_schedule_time')->nullable();
            $table->foreignId('clt_created_by')->constrained('app_users', 'user_id');
            $table->dateTime('clt_created_dt');
            $table->foreignId('clt_last_updated_by')->constrained('app_users', 'user_id');
            $table->dateTime('clt_last_updated_dt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_templates');
    }
};
