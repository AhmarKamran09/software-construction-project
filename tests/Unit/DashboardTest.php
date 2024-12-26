<?php

namespace Tests\Feature;

use App\Models\Order;
use App\Models\User;
use App\Http\Livewire\Dashboard;
use Livewire\Livewire;
use Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;

class DashboardTest extends TestCase
{
    use RefreshDatabase;

    /** @test */
    public function it_renders_the_dashboard_with_user_orders()
    {
        // Create a user
        $user = User::factory()->create();

        // Create some orders for the user
        $orders = Order::factory()->count(3)->create(['user_id' => $user->id]);

        // Act as the user and test the Livewire component
        $this->actingAs($user);

        Livewire::test(Dashboard::class)
            ->assertViewIs('livewire.dashboard')
            ->assertViewHas('orders', function ($viewOrders) use ($orders) {
                return $viewOrders->count() === $orders->count();
            });
    }

    /** @test */
    public function it_generates_an_invoice_pdf()
    {
        // Create a user and an order
        $user = User::factory()->create();
        $order = Order::factory()->create(['user_id' => $user->id]);

        // Mock the InvoiceService
        $invoiceService = $this->mock(\App\Services\InvoiceService::class);
        $invoiceService->shouldReceive('createInvoice')
            ->once()
            ->with($order)
            ->andReturn($this->getMockPdf());

        // Act as the user
        $this->actingAs($user);

        // Test the invoicePdf method
        Livewire::test(Dashboard::class)
            ->call('invoicePdf', $order, $invoiceService)
            ->assertSuccessful();
    }

    private function getMockPdf()
    {
        return new class {
            public function stream()
            {
                return response('Mock PDF Content', 200, ['Content-Type' => 'application/pdf']);
            }
        };
    }
}
