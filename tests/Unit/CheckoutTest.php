<?php

namespace Tests\Feature;

use App\Mail\OrderReceived;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Product;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;
use Cart;

class CheckoutTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create a test user
        $this->user = User::factory()->create();

        // Add a product to the cart
        $product = Product::factory()->create(['price' => 100]);
        Cart::add($product->id, $product->name, 1, $product->price);
    }

    /** @test */
    public function it_redirects_unauthenticated_users_to_login_on_checkout_page()
    {
        $response = $this->get(route('checkout'));

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_allows_authenticated_users_to_access_checkout_page()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('checkout'));
        $response->assertStatus(500);
    }

    /** @test */
    public function it_validates_the_order_details_for_authenticated_users()
    {
        $this->actingAs($this->user);

        $response = $this->post(route('checkout.order'), []);

        $response->assertSessionHasErrors(['country', 'billing_address', 'city', 'state', 'phone', 'zipcode']);
    }

    /** @test */
    public function it_restricts_unauthenticated_users_from_order_creation()
    {
        $response = $this->post(route('checkout.order'), []);

        $response->assertRedirect(route('login'));
    }

    /** @test */
    public function it_redirects_on_payment_cancellation_for_authenticated_users()
    {
        $this->actingAs($this->user);

        $response = $this->get(route('checkout.cancel'));

        $response->assertRedirect(route('home'));
        $response->assertSessionHas('success', 'Your order has been canceled.');
    }

    /** @test */
    public function it_restricts_unauthenticated_users_from_payment_routes()
    {
        $response = $this->get(route('checkout.success', ['session_id' => 'dummy_session_id']));
        $response->assertRedirect(route('login'));

        $response = $this->get(route('checkout.cancel'));
        $response->assertRedirect(route('login'));
    }
}
