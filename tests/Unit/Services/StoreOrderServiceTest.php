<?php

namespace Tests\Unit\Services;

use App\Models\Order;
use App\Models\Product;
use App\Services\StoreOrderService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreOrderServiceTest extends TestCase
{
    use RefreshDatabase;

    private StoreOrderService $service;

    protected function setUp(): void
    {
        parent::setUp();

        $this->service = new StoreOrderService(new Order());
    }

    public function test_it_creates_an_order_and_attaches_products()
    {
        $data = [
            'products' => [
                [
                    'product_id' => 1,
                    'quantity'   => 2,
                ],
            ],
        ];

        Product::factory()->create(['id' => 1]);

        $orderId = $this->service->createOrder($data);

        $this->assertDatabaseHas('orders', [
            'id' => $orderId,
        ]);

        $this->assertDatabaseHas('product_order', [
            'order_id'   => $orderId,
            'product_id' => 1,
            'quantity'   => 2,
        ]);
    }
}