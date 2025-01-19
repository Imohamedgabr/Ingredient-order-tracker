<?php

namespace Tests\Feature;

use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class StoreOrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public $seed = true;

    public function test_user_can_store_an_order()
    {
        $response = $this->post('/api/orders', [
            'products' => [
                [
                    'product_id' => 1,
                    'quantity' => 1,
                ],
            ],
        ]);

        $response->assertStatus(200)->assertJsonFragment([
            'message' => 'Order created successfully',
            'order_id' => 1,
        ]);

        $this->assertDatabaseHas('orders', [
            'id' => 1,
        ]);

        $this->assertDatabaseHas('product_order', [
            'order_id' => 1,
            'product_id' => 1,
            'quantity' => 1,
        ]);

        $product = Product::find(1);

        $remainingStock = $product->ingredients->first()->stock_capacity - $product->ingredients->first()->pivot->ingredient_amount;

        $this->assertDatabaseHas('ingredients', [
            'current_stock_amount' => $remainingStock,
            'id' => $product->ingredients->first()->id,
        ]);
    }

    public function test_user_cant_use_fake_product_id()
    {
        $response = $this->post('/api/orders', [
            'products' => [
                [
                    'product_id' => 5000,
                    'quantity' => 1,
                ],
            ],
        ], ['Accept' => 'application/json']);

        $response->assertStatus(422)->assertExactJson([
            'message' => 'The selected product is invalid.',
            'order_id' => null
        ]);
    }
}
