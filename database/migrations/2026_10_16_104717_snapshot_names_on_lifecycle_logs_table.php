<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Lifecycle logs are history: they must survive locations, equipment and users being renamed or deleted.
     * The location, allocated equipment and performer keep their id (no foreign key) plus a name snapshot
     * taken when the log was written. asset_id, action_id and status_id keep their foreign keys.
     */
    public function up(): void
    {
        Schema::table('lifecycle_logs', function (Blueprint $table) {
            $table->dropForeign(['deploy_to_loc_id']);
            $table->dropForeign(['allocate_to_equipment_id']);
            $table->dropForeign(['performed_by']);
        });

        Schema::table('lifecycle_logs', function (Blueprint $table) {
            $table->string('deploy_to_loc_name')->nullable()->after('deploy_to_loc_id');
            $table->string('allocate_to_equipment_name')->nullable()->after('allocate_to_equipment_id');
            $table->string('performed_by_name')->nullable()->after('performed_by');
        });

        $locations = DB::table('locations')->pluck('name', 'id');
        $equipment = DB::table('equipment_units')->pluck('eqm_name', 'eqm_id');
        $users = DB::table('app_users')
            ->get(['user_id', 'user_fname', 'user_mname', 'user_lname'])
            ->mapWithKeys(fn (object $user): array => [
                $user->user_id => trim(implode(' ', array_filter([$user->user_fname, $user->user_mname, $user->user_lname]))) ?: null,
            ]);

        DB::table('lifecycle_logs')
            ->select(['id', 'deploy_to_loc_id', 'allocate_to_equipment_id', 'performed_by'])
            ->orderBy('id')
            ->chunkById(500, function ($logs) use ($locations, $equipment, $users): void {
                foreach ($logs as $log) {
                    DB::table('lifecycle_logs')->where('id', $log->id)->update([
                        'deploy_to_loc_name' => $locations[$log->deploy_to_loc_id] ?? null,
                        'allocate_to_equipment_name' => $equipment[$log->allocate_to_equipment_id] ?? null,
                        'performed_by_name' => $users[$log->performed_by] ?? null,
                    ]);
                }
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('lifecycle_logs', function (Blueprint $table) {
            $table->dropColumn(['deploy_to_loc_name', 'allocate_to_equipment_name', 'performed_by_name']);
        });

        Schema::table('lifecycle_logs', function (Blueprint $table) {
            $table->foreign('deploy_to_loc_id')
                ->references('id')
                ->on('locations')
                ->restrictOnDelete();

            $table->foreign('allocate_to_equipment_id')
                ->references('eqm_id')
                ->on('equipment_units')
                ->restrictOnDelete();

            $table->foreign('performed_by')
                ->references('user_id')
                ->on('app_users')
                ->nullOnDelete();
        });
    }
};
