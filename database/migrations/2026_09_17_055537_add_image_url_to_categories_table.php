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
        Schema::table('categories', function (Blueprint $table) {
            // Full public HTTPS URL, not a private storage path — set by
            // CategoryController from the 'category_images' disk
            // (dynamic_quiz_category_images_brd.md §5).
            $table->string('image_url')->nullable()->after('icon');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('categories', function (Blueprint $table) {
            $table->dropColumn('image_url');
        });
    }
};
