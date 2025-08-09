<?php

namespace Tests\Feature;

use App\Services\MediaLibraryService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Crm\Models\Product;
use Tests\TestCase;

class MediaLibraryTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('media');
    }

    /** @test */
    public function it_can_upload_media_to_a_model()
    {
        // Create a product
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
        ]);

        // Create a fake image
        $file = UploadedFile::fake()->image('product.jpg');

        // Create a request with the file
        $request = request()->merge([
            'product_image' => $file,
        ]);

        // Use our service to upload the file
        $mediaService = new MediaLibraryService();
        $mediaService->uploadFromRequest($product, $request, 'product_image', 'product_image');

        // Assert that the file was stored
        $this->assertNotNull($product->getFirstMedia('product_image'));

        // Test our custom method
        $this->assertNotNull($product->getMediaUrl('product_image'));

        // Test conversions
        $this->assertNotNull($product->getMediaUrl('product_image', 'thumb'));
        $this->assertNotNull($product->getMediaUrl('product_image', 'medium'));
        $this->assertNotNull($product->getMediaUrl('product_image', 'large'));
    }

    /** @test */
    public function it_can_clear_media_collection()
    {
        // Create a product
        $product = Product::factory()->create([
            'name' => 'Test Product',
            'price' => 99.99,
        ]);

        // Create a fake image
        $file = UploadedFile::fake()->image('product.jpg');

        // Create a request with the file
        $request = request()->merge([
            'product_image' => $file,
        ]);

        // Use our service to upload the file
        $mediaService = new MediaLibraryService();
        $mediaService->uploadFromRequest($product, $request, 'product_image', 'product_image');

        // Assert that the file was stored
        $this->assertNotNull($product->getFirstMedia('product_image'));

        // Clear the media collection
        $mediaService->clearMediaCollection($product, 'product_image');

        // Assert that the file was removed
        $this->assertNull($product->getFirstMedia('product_image'));
    }
}
