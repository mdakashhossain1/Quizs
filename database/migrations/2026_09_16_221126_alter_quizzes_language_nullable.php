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
        // NULL now means "bilingual" (see question_translations/option_translations)
        // rather than a fixed single-language quiz — existing 'en'/'hi' quizzes
        // are untouched (bilingual_question_management_prd.md: leave existing
        // quizzes as-is, add the new model for quizzes going forward).
        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('language', 5)->nullable()->default(null)->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::table('quizzes')->whereNull('language')->update(['language' => 'en']);

        Schema::table('quizzes', function (Blueprint $table) {
            $table->string('language', 5)->default('en')->nullable(false)->change();
        });
    }
};
