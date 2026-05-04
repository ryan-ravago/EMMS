<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AppSettingsSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        DB::table('app_settings')->upsert(
            [
                [
                    'key' => 'sap_sync_time',
                    'value' => '01:00',
                    'created_at' => now(),
                    'updated_at' => now()
                ],
            ],
            ['key'],
            ['value']
        );
    }
}
