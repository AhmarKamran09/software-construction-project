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

class CheckoutIntegrationTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_allows_user_to_complete_checkout_workflow()
    {
        // Set up
        Mail::fake();
        $user = User::factory()->create();
        $this->actingAs($user);

        $product = Product::factory()->create(['price' => 100]);
        Cart::add($product->id, $product->name, 1, $product->price);

        // Step 1: Submit order details
        $orderDetails = [
            'country' => 'USA',
            'billing_address' => '123 Test St.',
            'city' => 'Testville',
            'state' => 'TS',
            'phone' => '1234567890',
            'zipcode' => '12345',
        ];

        $response = $this->post(route('checkout.order'), $orderDetails);

        // Assert order is created
        $this->assertDatabaseHas('orders', [
            'user_id' => $user->id,
            'status' => 'pending',
        ]);

        $order = Order::first();

        // Step 2: Simulate Stripe success response
        $this->mock(\Stripe\Checkout\Session::class, function ($mock) {
            $mock->shouldReceive('retrieve')->andReturn((object)[
                'id' => 'test_session_id',
                'customer' => 'test_customer_id',
            ]);
        });

        $response = $this->get(route('checkout.success', ['session_id' => 'test_session_id']));
        // $response->assertStatus(200);

        // Assert order status is updated
        $this->assertEquals('processing', $order->fresh()->status);

        // Assert email is sent
        Mail::assertSent(OrderReceived::class, function ($mail) use ($order) {
            return $mail->order->id === $order->id;
        });

        // Step 3: Assert cart is cleared
        $this->assertEquals(0, Cart::count());
    }
}
