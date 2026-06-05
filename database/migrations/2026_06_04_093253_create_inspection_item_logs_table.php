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
        Schema::create('inspection_item_logs', function (Blueprint $table) {
            $table->id('inil_id');

            $table->foreignId('inil_insi_id')
                ->constrained('inspection_items', 'insi_id')
                ->restrictOnDelete();

            $table->string('inil_action_made', 10);

            $table->string('inil_status_log', 40);

            $table->text('inil_remarks')
                ->nullable();

            $table->foreignId('inil_by')
                ->nullable()
                ->constrained('app_users', 'user_id')
                ->nullOnDelete();

            $table->dateTime('inil_dt');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('inspection_item_logs');
    }
};
