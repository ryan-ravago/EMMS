<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * The Service Vehicles page lists assets of type SRVV; make sure the type exists everywhere.
     */
    public function up(): void
    {
        if (DB::table('asset_types')->where('code', 'SRVV')->exists()) {
            return;
        }

        if (DB::table('asset_types')->where('name', 'Service Vehicle')->exists()) {
            DB::table('asset_types')->where('name', 'Service Vehicle')->update(['code' => 'SRVV']);

            return;
        }

        DB::table('asset_types')->insert(['code' => 'SRVV', 'name' => 'Service Vehicle']);
    }

    /**
     * Left in place on rollback: assets may already use the type.
     */
    public function down(): void
    {
        //
    }
};
