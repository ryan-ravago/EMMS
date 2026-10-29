<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row per equipment trip: the OUT scan and the IN scan that belong together, plus how the trip was verified.
     * The raw scans stay in asset_tag_logs; manual times are only stored here.
     */
    public function up(): void
    {
        Schema::create('asset_movements', function (Blueprint $table) {
            $table->id();

            $table->foreignId('asset_id')->constrained('equipment_units', 'eqm_id')->restrictOnDelete();
            $table->string('tag_id')->nullable();
            $table->foreign('tag_id')->references('tag_id')->on('asset_tags')->restrictOnDelete();

            $table->foreignId('out_log_id')->nullable()->unique()->constrained('asset_tag_logs')->restrictOnDelete();
            $table->foreignId('in_log_id')->nullable()->unique()->constrained('asset_tag_logs')->restrictOnDelete();

            $table->dateTime('time_out')->nullable();
            $table->dateTime('time_in')->nullable();
            $table->foreignId('yard_out_id')->nullable()->constrained('locations')->restrictOnDelete();
            $table->foreignId('yard_in_id')->nullable()->constrained('locations')->restrictOnDelete();

            $table->foreignId('manual_out_by')->nullable()->constrained('app_users', 'user_id')->nullOnDelete();
            $table->dateTime('manual_out_at')->nullable();
            $table->text('manual_out_remarks')->nullable();
            $table->foreignId('manual_in_by')->nullable()->constrained('app_users', 'user_id')->nullOnDelete();
            $table->dateTime('manual_in_at')->nullable();
            $table->text('manual_in_remarks')->nullable();

            $table->foreignId('acknowledged_by')->nullable()->constrained('app_users', 'user_id')->nullOnDelete();
            $table->dateTime('acknowledged_at')->nullable();
            $table->text('acknowledgement_remarks')->nullable();

            $table->foreignId('disregarded_by')->nullable()->constrained('app_users', 'user_id')->nullOnDelete();
            $table->dateTime('disregarded_at')->nullable();
            $table->text('disregard_remarks')->nullable();

            $table->timestamps();

            $table->index(['asset_id', 'time_in']);
            $table->index('time_out');
            $table->index('acknowledged_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asset_movements');
    }
};
