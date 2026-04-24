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
        Schema::create('app_users', function (Blueprint $table) {
            $table->id('user_id');
            $table->string('user_fname');
            $table->string('user_mname');
            $table->string('user_lname');
            $table->string('user_email')->unique();
            $table->string('user_contact_no')->nullable();
            $table->text('user_fb_profile_link')->nullable();

            $table->unsignedBigInteger('user_dep_id');

            $table->foreign('user_dep_id')
                ->references('dep_id')
                ->on('departments')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('app_users');
    }
};
