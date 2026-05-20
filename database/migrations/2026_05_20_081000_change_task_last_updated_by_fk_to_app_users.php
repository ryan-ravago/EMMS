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
        Schema::table('tasks', function (Blueprint $table) {
            // Drop existing foreign key to departments (if present)
            $table->dropForeign(['task_last_updated_by']);

            // Add foreign key to app_users.user_id
            $table->foreign('task_last_updated_by')
                ->references('user_id')
                ->on('app_users')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('tasks', function (Blueprint $table) {
            // Drop FK to app_users
            $table->dropForeign(['task_last_updated_by']);

            // Restore FK to departments.dep_id
            $table->foreign('task_last_updated_by')
                ->references('dep_id')
                ->on('departments')
                ->onDelete('restrict');
        });
    }
};
