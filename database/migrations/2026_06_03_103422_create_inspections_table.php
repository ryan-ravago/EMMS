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
        Schema::create('inspections', function (Blueprint $table) {
            $table->id('ins_id');

            $table->unsignedBigInteger('ins_dep_id');
            $table->foreign('ins_dep_id')->references('dep_id')->on('departments')->restrictOnDelete();

            $table->unsignedBigInteger('ins_eqm_id');
            $table->foreign('ins_eqm_id')->references('eqm_id')->on('equipment_units')->restrictOnDelete();

            $table->unsignedBigInteger('ins_by');
            $table->foreign('ins_by')->references('user_id')->on('app_users')->restrictOnDelete();

            $table->dateTime('ins_dt');

            $table->unsignedBigInteger('ins_submitted_by')->nullable();
            $table->foreign('ins_submitted_by')->references('user_id')->on('app_users')->restrictOnDelete();

            $table->dateTime('ins_submitted_dt')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspections');
    }
};
