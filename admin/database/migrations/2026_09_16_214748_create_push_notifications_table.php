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
        Schema::create('push_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('title');
            $table->text('body');
            $table->string('thumbnail_url')->nullable();
            $table->enum('target_type', ['all', 'single', 'selected']);
            $table->enum('destination_type', ['none', 'attendance', 'quiz_details', 'target_progress', 'achievement', 'profile'])->default('none');
            $table->unsignedBigInteger('destination_id')->nullable();
            $table->json('payload_data')->nullable();
            $table->enum('status', ['sent', 'failed'])->default('sent');
            $table->foreignId('created_by')->nullable()->constrained('users')->nullOnDelete();
            $table->timestamp('sent_at')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('push_notifications');
    }
};
