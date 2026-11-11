<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Some assets (accessories such as boom inserts with a serial number) are known only by a long name, so the
     * asset code has to hold it. The unique index stays; the column keeps its other attributes (required).
     */
    public function up(): void
    {
        Schema::table('equipment_units', function (Blueprint $table) {
            $table->string('eqm_prc_code', 255)->change();
        });
    }

    /**
     * Not reversed: shortening the column would cut or reject the long codes now stored in it.
     */
    public function down(): void
    {
        //
    }
};
