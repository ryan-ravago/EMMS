<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * One row: where the RFID reader alert currently stands, so an alert is sent once (then reminded)
     * instead of on every check, and survives a cache clear or a deploy.
     */
    public function up(): void
    {
        Schema::create('rfid_health_states', function (Blueprint $table) {
            $table->id();
            $table->string('status', 20)->default('ok');
            $table->string('cause', 40)->nullable();
            $table->dateTime('since')->nullable();
            $table->dateTime('last_notified_at')->nullable();
            $table->dateTime('token_warning_sent_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('rfid_health_states');
    }
};
