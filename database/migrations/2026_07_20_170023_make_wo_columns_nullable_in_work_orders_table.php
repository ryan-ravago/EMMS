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
        Schema::table('work_orders', function (Blueprint $table) {
            // Makes the columns nullable
            $table->text('wo_root_cause')->nullable()->change();
            $table->text('wo_corrective_action')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('work_orders', function (Blueprint $table) {
            // Reverts the columns back to NOT NULL
            $table->text('wo_root_cause')->nullable(false)->change();
            $table->text('wo_corrective_action')->nullable(false)->change();
        });
    }
};
