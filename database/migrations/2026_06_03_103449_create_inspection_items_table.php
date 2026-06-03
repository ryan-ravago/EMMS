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
        Schema::create('inspection_items', function (Blueprint $table) {
            $table->id('insi_id');

            $table->unsignedBigInteger('insi_ins_id');
            $table->foreign('insi_ins_id')->references('ins_id')->on('inspections')->cascadeOnDelete();

            $table->unsignedBigInteger('insi_task_id')->nullable();
            $table->foreign('insi_task_id')->references('task_id')->on('tasks')->nullOnDelete();

            $table->char('insi_result', 1)->nullable();
            $table->foreign('insi_result')->references('insr_code')->on('inspection_results')->restrictOnDelete();

            $table->string('insi_cli_name_for_record');
            $table->text('insi_remarks')->nullable();

            $table->string('insi_a_id', 10)->nullable();
            $table->foreign('insi_a_id')->references('a_id')->on('actions')->restrictOnDelete();

            $table->string('insi_action_made')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_items');
    }
};
