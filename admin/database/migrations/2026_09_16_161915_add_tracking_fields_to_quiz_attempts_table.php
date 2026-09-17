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
            $table->timestamp('started_at')->nullable()->after('quiz_id');
            $table->enum('status', ['in_progress', 'completed'])->default('completed')->after('started_at');
            $table->integer('attempted_questions')->default(0)->after('total_questions');
            $table->integer('wrong_answers')->default(0)->after('correct_answers');
            $table->integer('unanswered_questions')->default(0)->after('wrong_answers');
            $table->decimal('accuracy', 5, 2)->nullable()->after('unanswered_questions');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('quiz_attempts', function (Blueprint $table) {
            $table->dropColumn([
                'started_at',
                'status',
                'attempted_questions',
                'wrong_answers',
                'unanswered_questions',
                'accuracy',
            ]);
        });
    }
};
