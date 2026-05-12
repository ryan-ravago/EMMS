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
        Schema::table('equipment_checklist_assignments', function (Blueprint $table) {
            $table->unsignedBigInteger('eca_dep_id')->nullable()->after('eca_id');

            $table->foreign('eca_dep_id')
                ->references('dep_id')
                ->on('departments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('equipment_checklist_assignments', function (Blueprint $table) {
            $table->dropForeign(['eca_dep_id']);
            $table->dropColumn('eca_dep_id');
        });
    }
};
