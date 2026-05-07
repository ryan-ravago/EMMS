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
        Schema::create('checklist_items', function (Blueprint $table) {
            $table->id('cli_id');
            $table->unsignedBigInteger('cli_clt_id')->constrained('checklist_templates', 'clt_id')->restrictOnDelete();
            $table->string('cli_name');
            $table->integer('cli_sort_order');
            $table->unsignedInteger('cli_created_by')->constrained('app_users', 'user_id')->restrictOnDelete();
            $table->dateTime('cli_created_at');
            $table->unsignedInteger('cli_last_updated_by')->constrained('app_users', 'user_id')->restrictOnDelete();
            $table->dateTime('cli_last_updated_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('checklist_items');
    }
};
