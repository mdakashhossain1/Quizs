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
        Schema::create('question_translations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('question_id')->constrained()->cascadeOnDelete();
            $table->string('language_code', 5);
            $table->text('question_text');
            $table->text('meaning')->nullable();
            $table->text('explanation')->nullable();
            $table->string('image')->nullable();
            $table->timestamps();

            $table->unique(['question_id', 'language_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('question_translations');
    }
};
