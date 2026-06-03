<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('inspection_results', function (Blueprint $table) {
            $table->char('insr_code', 1)->primary();
            $table->string('insr_name', 10);
            $table->string('insr_color', 20);
        });

        DB::table('inspection_results')->insert([
            ['insr_code' => 'P', 'insr_name' => 'Passed', 'insr_color' => 'success'],
            ['insr_code' => 'F', 'insr_name' => 'Failed', 'insr_color' => 'danger'],
            ['insr_code' => 'N', 'insr_name' => 'N/A',    'insr_color' => 'gray'],
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_results');
    }
};
