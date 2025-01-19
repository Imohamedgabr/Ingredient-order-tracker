<?php

namespace Tests\Unit\Services;

use App\Models\Product;
use App\Services\StoreOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    public function test_it_creates_an_order_and_attaches_products()
    {
        $products = Product::factory()->count(2)->create();

        $data = [
            'products' => $products->map(fn ($product) => [
                'product_id' => $product->id,
                'quantity'   => 2,
            ])->toArray(),
        ];

        $service = new StoreOrderService();
        $orderId = $service->createOrder($data);

        $this->assertDatabaseHas('orders', ['id' => $orderId]);

        foreach ($products as $product) {
            $this->assertDatabaseHas('product_order', [
                'order_id'   => $orderId,
                'product_id' => $product->id,
                'quantity'   => 2,
            ]);
        }
    }
}
