<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Modules\Crm\Models\Product;
use Tests\TestCase;

class ProductMediaTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
    }

    /** @test */
    public function it_can_add_media_to_product()
    {
        // Create a product directly
        $product = new Product([
            'name' => 'Test Product',
            'sku' => 'TP-001',
            'description' => 'A test product',
            'price' => 99.99,
            'type' => 'physical',
            'is_active' => true,
        ]);
        $product->save();

        // Create a fake image
        $file = UploadedFile::fake()->image('product.jpg');

        // Add the image to the product
        $product->addMedia($file)
            ->toMediaCollection('product_image');

        // Assert the media was added
        $this->assertCount(1, $product->getMedia('product_image'));

        // Get the media
        $media = $product->getFirstMedia('product_image');

        // Assert the media is an instance of our custom Media class
        $this->assertInstanceOf(\App\Models\Media::class, $media);

        // Test the getMediaUrl method
        $url = $product->getMediaUrl('product_image');
        $this->assertNotNull($url);

        // Test the getAllMediaUrls method
        $urls = $product->getAllMediaUrls('product_image');
        $this->assertIsArray($urls);
        $this->assertCount(1, $urls);
    }
}
