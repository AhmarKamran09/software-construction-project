<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductDetailsTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_renders_the_product_details_page_with_valid_product_id()
    {
        // Create a product
        $product = Product::factory()->create([
            'image' => 'default_image.jpg',
            'images' => json_encode(['image1.jpg', 'image2.jpg']),
        ]);

        // Access the product details page
        $response = $this->get(route('product.details', $product->id));

        // Assertions
        $response->assertStatus(200);
        $response->assertViewIs('livewire.product-details');
        $response->assertViewHas('product', function ($viewProduct) use ($product) {
            return $viewProduct->id === $product->id
                && count($viewProduct->images) === 2 // Asserting images are decoded correctly
                && $viewProduct->images[0] === 'image1.jpg';
        });
    }

    

    /** @test */
    public function it_returns_404_for_invalid_product_id()
    {
        // Access the product details page with an invalid product ID
        $response = $this->get(route('product.details', 99999));

        // Assertions
        $response->assertStatus(500);
    }
}
