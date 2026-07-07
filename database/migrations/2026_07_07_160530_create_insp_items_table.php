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
        Schema::create('insp_items', function (Blueprint $table) {
            $table->string('inspi_id')->primary();
            $table->string('inspi_insp_id');
            $table->foreign('inspi_insp_id')->references('insp_id')->on('insps')->cascadeOnDelete();

            $table->string('inspi_task')->nullable();
            $table->enum('inspi_result', ['Passed', 'Failed', 'N/A'])->nullable();
            $table->longText('inspi_remarks')->nullable();

            $table->index('inspi_insp_id');
            $table->index('inspi_result');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('insp_items');
    }
};
