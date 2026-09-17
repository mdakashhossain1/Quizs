<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\File;
use Tests\TestCase;

/**
 * dynamic_quiz_category_images_brd.md: category images are admin-uploaded,
 * stored under the public uploads directory (shared-hosting requirement),
 * and returned as a full public URL by the category API — never hardcoded
 * in Flutter.
 */
class CategoryImageTest extends TestCase
{
    use RefreshDatabase;

    protected function tearDown(): void
    {
        File::deleteDirectory(public_path('uploads/categories'));
        parent::tearDown();
    }

    public function test_admin_can_upload_a_category_image(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'web')->post(route('admin.categories.store'), [
            'name' => 'Science & Nature',
            'color' => '#2563eb',
            'sort_order' => 0,
            'image' => UploadedFile::fake()->image('science.jpg'),
        ])->assertRedirect(route('admin.categories.index'));

        $category = Category::firstOrFail();
        $this->assertNotNull($category->image_url);
        $this->assertStringContainsString('/uploads/categories/', $category->image_url);
    }

    public function test_admin_can_replace_a_category_image(): void
    {
        $admin = User::factory()->admin()->create();
        $category = Category::create(['name' => 'GK', 'slug' => 'gk-' . uniqid(), 'color' => '#000', 'sort_order' => 0, 'is_active' => true]);

        $this->actingAs($admin, 'web')->post(route('admin.categories.store'), [
            'name' => 'GK 2', 'color' => '#000', 'sort_order' => 0,
            'image' => UploadedFile::fake()->image('first.jpg'),
        ]);
        $created = Category::whereNotNull('image_url')->firstOrFail();
        $originalUrl = $created->image_url;

        $this->actingAs($admin, 'web')->put(route('admin.categories.update', $created), [
            'name' => $created->name, 'color' => '#000', 'sort_order' => 0,
            'image' => UploadedFile::fake()->image('second.jpg'),
        ])->assertRedirect();

        $created->refresh();
        $this->assertNotSame($originalUrl, $created->image_url);
        $this->assertFalse(File::exists(public_path('uploads/categories/' . basename($originalUrl))), 'The replaced image file should be deleted.');
    }

    public function test_admin_can_remove_a_category_image(): void
    {
        $admin = User::factory()->admin()->create();

        $this->actingAs($admin, 'web')->post(route('admin.categories.store'), [
            'name' => 'Mathematics', 'color' => '#000', 'sort_order' => 0,
            'image' => UploadedFile::fake()->image('math.jpg'),
        ]);
        $category = Category::whereNotNull('image_url')->firstOrFail();
        $imagePath = public_path('uploads/categories/' . basename($category->image_url));
        $this->assertTrue(File::exists($imagePath));

        $this->actingAs($admin, 'web')->put(route('admin.categories.update', $category), [
            'name' => $category->name, 'color' => '#000', 'sort_order' => 0,
            'remove_image' => '1',
        ])->assertRedirect();

        $this->assertNull($category->fresh()->image_url);
        $this->assertFalse(File::exists($imagePath));
    }

    public function test_category_api_returns_the_public_image_url(): void
    {
        Category::create([
            'name' => 'History', 'slug' => 'history-' . uniqid(), 'color' => '#000',
            'image_url' => 'http://localhost/uploads/categories/history.jpg',
            'sort_order' => 0, 'is_active' => true,
        ]);

        $categories = $this->getJson('/api/categories')->assertOk()->json('categories');

        $this->assertSame('http://localhost/uploads/categories/history.jpg', $categories[0]['image_url']);
    }

    public function test_category_without_an_image_returns_null_not_a_hardcoded_fallback(): void
    {
        Category::create([
            'name' => 'No Image Category', 'slug' => 'no-image-' . uniqid(), 'color' => '#000',
            'sort_order' => 0, 'is_active' => true,
        ]);

        $categories = $this->getJson('/api/categories')->assertOk()->json('categories');

        $this->assertNull($categories[0]['image_url']);
    }

    public function test_non_admin_cannot_upload_a_category_image(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user, 'web')->post(route('admin.categories.store'), [
            'name' => 'x', 'color' => '#000', 'sort_order' => 0,
        ])->assertRedirect(route('admin.login'));
    }
}
