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
        Schema::create('worker_reports', function (Blueprint $table) {
            $table->unsignedBigInteger('wr_rs_id');
            $table->unsignedBigInteger('wr_worker_id');

            // Composite primary key
            $table->primary(['wr_rs_id', 'wr_worker_id']);

            $table->foreign('wr_rs_id')
                ->references('rs_id')
                ->on('report_submissions')
                ->onDelete('cascade');

            $table->foreign('wr_worker_id')
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
        Schema::dropIfExists('worker_reports');
    }
};
