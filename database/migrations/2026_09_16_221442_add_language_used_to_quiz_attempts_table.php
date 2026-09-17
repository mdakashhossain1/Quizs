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
        Schema::table('quiz_attempts', function (Blueprint $table) {
            // Optional, for analytics only (bilingual_question_management_prd.md
            // §11) — never used to determine correctness, which stays keyed by
            // option id regardless of which language the user answered in.
            $table->string('language_used', 5)->nullable()->after('quiz_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn('language_used');
        });
    }
};
