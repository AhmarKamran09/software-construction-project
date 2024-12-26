<?php

namespace Tests\Feature;

use App\Models\Product;
use Livewire\Livewire;
use Tests\TestCase;
use Gloudemans\Shoppingcart\Facades\Cart as CartFacade;
use Illuminate\Foundation\Testing\RefreshDatabase;

class CartTest extends TestCase
{
  use RefreshDatabase;

  /** @test */
  public function it_can_add_item_to_cart()
  {
    // Create a sample product
    $product = Product::factory()->create([
      'name' => 'Sample Product',
      'price' => 100,
    ]);

    // Call the addToCart method
    $response = $this->post(route('cart.add'), [
      'product_id' => $product->id,
    ]);

    // Assert item was added to the cart
    $this->assertCount(1, CartFacade::content());
    $cartItem = CartFacade::content()->first();

    $this->assertEquals($product->id, $cartItem->id);
    $this->assertEquals($product->name, $cartItem->name);
    $this->assertEquals($product->price, $cartItem->price);
  }

  /** @test */
  public function it_can_increment_item_quantity_in_cart()
  {
    // Add an item to the cart
    $product = Product::factory()->create([
      'name' => 'Sample Product',
      'price' => 100,
    ]);

    CartFacade::add($product->id, $product->name, 1, $product->price);

    $cartItem = CartFacade::content()->first();

    // Call the incQty method
    $response = $this->post(route('qty.up'), [
      'row_id' => $cartItem->rowId,
    ]);

    // Assert the quantity was incremented
    $updatedItem = CartFacade::get($cartItem->rowId);
    $this->assertEquals(2, $updatedItem->qty);
  }

  /** @test */
  public function it_can_decrement_item_quantity_in_cart()
  {
    // Add an item to the cart
    $product = Product::factory()->create([
      'name' => 'Sample Product',
      'price' => 100,
    ]);

    CartFacade::add($product->id, $product->name, 2, $product->price);

    $cartItem = CartFacade::content()->first();

    // Call the decQty method
    $response = $this->post(route('qty.down'), [
      'row_id' => $cartItem->rowId,
    ]);

    // Assert the quantity was decremented
    $updatedItem = CartFacade::get($cartItem->rowId);
    $this->assertEquals(1, $updatedItem->qty);
  }

  /** @test */
  public function it_can_remove_item_from_cart()
  {
    // Add an item to the cart
    $product = Product::factory()->create(['name' => 'Sample Product', 'price' => 100]);
    CartFacade::add($product->id, $product->name, 1, $product->price);

    $cartItem = CartFacade::content()->first();

    // Call the destroyItem method
    $response = $this->delete(route('destroy.item'), [
      'row_id' => $cartItem->rowId,
    ]);

    // Refresh CartFacade to reflect the state after the request
    $this->assertCount(0, CartFacade::content());
  }


  /** @test */
  public function it_can_clear_the_cart()
  {
    // Add multiple items to the cart
    $product1 = Product::factory()->create(['name' => 'Product 1', 'price' => 100]);
    $product2 = Product::factory()->create(['name' => 'Product 2', 'price' => 200]);

    CartFacade::add($product1->id, $product1->name, 1, $product1->price);
    CartFacade::add($product2->id, $product2->name, 1, $product2->price);

    // Call the destroyCart method
    $response = $this->delete(route('destroy.cart'));

    // Assert the cart was cleared
    $this->assertCount(0, CartFacade::content());
  }
}
