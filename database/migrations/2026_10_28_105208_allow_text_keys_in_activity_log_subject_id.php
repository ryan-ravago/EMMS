<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Some logged models have a text primary key (an RFID tag's id is its tag text), which a number
     * column cannot hold: updating such a record failed with "Incorrect integer value". Numeric ids still
     * fit in the text column, and the (subject_type, subject_id) index is kept.
     */
    public function up(): void
    {
        Schema::table('activity_log', function (Blueprint $table) {
            $table->string('subject_id', 64)->nullable()->change();
        });
    }

    /**
     * Left as text on rollback: shrinking it back would drop the log rows of text-keyed records.
     */
    public function down(): void
    {
        //
    }
};
