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
        Schema::table('work_order_assignments', function (Blueprint $table) {
            $table->dropForeign(['woa_worker_id']);
        });

        Schema::table('work_order_assignments', function (Blueprint $table) {
            $table->foreign('woa_worker_id')
                ->references('user_id')
                ->on('app_users')
                ->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_order_assignments', function (Blueprint $table) {
            $table->dropForeign(['woa_worker_id']);
        });

        Schema::table('work_order_assignments', function (Blueprint $table) {
            $table->foreign('woa_worker_id')
                ->references('user_id')
                ->on('app_users')
                ->cascadeOnDelete();
        });
    }
};
