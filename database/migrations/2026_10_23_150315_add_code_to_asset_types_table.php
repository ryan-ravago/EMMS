<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Asset types are looked up by this code instead of by their id or name.
     *
     * @var array<string, string>
     */
    private const CODES_BY_NAME = [
        'Equipment' => 'EQM',
        'Accessory' => 'ACC',
        'Service Vehicle' => 'SRVV',
    ];

    public function up(): void
    {
        Schema::table('asset_types', function (Blueprint $table) {
            $table->string('code', 10)->nullable()->after('id');
        });

        foreach (self::CODES_BY_NAME as $name => $code) {
            DB::table('asset_types')->where('name', $name)->update(['code' => $code]);
        }

        // Any other type already in the table gets a placeholder so the column can be made required.
        DB::table('asset_types')->whereNull('code')->orderBy('id')->each(
            fn (object $type) => DB::table('asset_types')->where('id', $type->id)->update(['code' => 'TYPE'.$type->id])
        );

        Schema::table('asset_types', function (Blueprint $table) {
            $table->string('code', 10)->nullable(false)->change();
            $table->unique('code');
        });
    }

    public function down(): void
    {
        Schema::table('asset_types', function (Blueprint $table) {
            $table->dropUnique(['code']);
            $table->dropColumn('code');
        });
    }
};
