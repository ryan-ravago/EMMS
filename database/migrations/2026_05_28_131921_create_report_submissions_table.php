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
        Schema::create('report_submissions', function (Blueprint $table) {
            $table->bigIncrements('rs_id');
            $table->unsignedBigInteger('rs_wo_id');
            $table->date('rs_work_date');
            $table->unsignedBigInteger('rs_submitted_by');
            $table->datetime('rs_submitted_dt');

            $table->foreign('rs_wo_id')
                ->references('wo_id')
                ->on('work_orders')
                ->onDelete('cascade');

            $table->foreign('rs_submitted_by')
                ->references('user_id')
                ->on('app_users')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('report_submissions');
    }
};
