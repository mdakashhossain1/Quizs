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
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('user')->after('email'); // 'admin' or 'user'
            $table->string('avatar')->nullable()->after('role');
            $table->string('google_id')->nullable()->after('avatar');
            $table->integer('streak')->default(0)->after('google_id');
            $table->integer('score')->default(0)->after('streak');
            $table->boolean('is_active')->default(true)->after('score');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn(['role', 'avatar', 'google_id', 'streak', 'score', 'is_active']);
        });
    }
};
