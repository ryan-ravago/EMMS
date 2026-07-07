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
        Schema::table('insps', function (Blueprint $table) {
            $table->unsignedBigInteger('insp_dep_id_fk')->nullable()->after('insp_no');
        });

        DB::statement(<<<'SQL'
            UPDATE insps i
            LEFT JOIN departments d_code
                ON d_code.dep_code = i.insp_dep_id
            LEFT JOIN departments d_id
                ON d_id.dep_id = CAST(i.insp_dep_id AS UNSIGNED)
            SET i.insp_dep_id_fk = COALESCE(d_code.dep_id, d_id.dep_id)
            WHERE i.insp_dep_id IS NOT NULL
              AND i.insp_dep_id <> ''
        SQL);

        DB::statement(<<<'SQL'
            UPDATE insps i
            LEFT JOIN equipment_units e
                ON e.eqm_id = i.insp_eqm_id
            SET i.insp_eqm_id = NULL
            WHERE i.insp_eqm_id IS NOT NULL
              AND e.eqm_id IS NULL
        SQL);

        Schema::table('insps', function (Blueprint $table) {
            $table->dropIndex('insps_insp_dep_id_index');
            $table->dropColumn('insp_dep_id');
        });

        Schema::table('insps', function (Blueprint $table) {
            $table->renameColumn('insp_dep_id_fk', 'insp_dep_id');
        });

        Schema::table('insps', function (Blueprint $table) {
            $table->index('insp_dep_id');
            $table->foreign('insp_dep_id')->references('dep_id')->on('departments')->restrictOnDelete();
            $table->foreign('insp_eqm_id')->references('eqm_id')->on('equipment_units')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('insps', function (Blueprint $table) {
            $table->dropForeign('insps_insp_dep_id_foreign');
            $table->dropForeign('insps_insp_eqm_id_foreign');

            $table->dropIndex('insps_insp_dep_id_index');

            $table->string('insp_dep_code')->nullable()->after('insp_no');
        });

        DB::statement(<<<'SQL'
            UPDATE insps i
            LEFT JOIN departments d
                ON d.dep_id = i.insp_dep_id
            SET i.insp_dep_code = d.dep_code
        SQL);

        Schema::table('insps', function (Blueprint $table) {
            $table->dropColumn('insp_dep_id');
        });

        Schema::table('insps', function (Blueprint $table) {
            $table->renameColumn('insp_dep_code', 'insp_dep_id');
            $table->index('insp_dep_id');
        });
    }
};
