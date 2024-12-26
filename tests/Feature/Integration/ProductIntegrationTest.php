<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProductIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_displays_product_details_with_images()
    {
        // Set up
        $user = User::factory()->create();
        $product = Product::factory()->create();

        // Acting as the user
        $this->actingAs($user);

        // Access product details page
        $response = $this->get(route('product.details', $product->id));

        // Assert product details are displayed correctly
        $response->assertStatus(200);
        $response->assertViewHas('product', function ($viewProduct) use ($product) {
            return $viewProduct->id === $product->id;
        });

        // Check if product data is rendered
        $response->assertSee($product->name);
        $response->assertSee($product->price);
        
        // If images are part of the product, verify they are displayed
        if ($product->images) {
            $images = json_decode($product->images);
            foreach ($images as $image) {
                $response->assertSee($image);
            }
        }
    }
}
