<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('app_users', function (Blueprint $table): void {
            $table->text('google_access_token')->nullable()->after('user_dep_id');
            $table->text('google_refresh_token')->nullable()->after('google_access_token');
            $table->dateTime('google_token_expires_at')->nullable()->after('google_refresh_token');
        });

        Schema::table('work_order_log_updates', function (Blueprint $table): void {
            $table->string('wolu_source', 32)->default('ui')->after('wolu_dt');
            $table->string('wolu_gmail_message_id')->nullable()->after('wolu_source');
            $table->string('wolu_gmail_thread_id')->nullable()->after('wolu_gmail_message_id');
            $table->string('wolu_sender_email')->nullable()->after('wolu_gmail_thread_id');
            $table->string('wolu_reply_subject')->nullable()->after('wolu_sender_email');
            $table->string('wolu_reply_token')->nullable()->after('wolu_reply_subject');

            $table->unique('wolu_gmail_message_id', 'work_order_log_updates_gmail_message_id_unique');
            $table->index(['wolu_wo_id', 'wolu_source'], 'work_order_log_updates_wo_source_index');
            $table->index('wolu_reply_token', 'work_order_log_updates_reply_token_index');
        });
    }

    public function down(): void
    {
        Schema::table('work_order_log_updates', function (Blueprint $table): void {
            $table->dropUnique('work_order_log_updates_gmail_message_id_unique');
            $table->dropIndex('work_order_log_updates_wo_source_index');
            $table->dropIndex('work_order_log_updates_reply_token_index');
            $table->dropColumn([
                'wolu_source',
                'wolu_gmail_message_id',
                'wolu_gmail_thread_id',
                'wolu_sender_email',
                'wolu_reply_subject',
                'wolu_reply_token',
            ]);
        });

        Schema::table('app_users', function (Blueprint $table): void {
            $table->dropColumn([
                'google_access_token',
                'google_refresh_token',
                'google_token_expires_at',
            ]);
        });
    }
};
