<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Product;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class OrderTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_creation_updates_stock()
    {
        $beef = Ingredient::create(['name' => 'Beef', 'stock_capacity' => 20000, 'current_stock_amount' => 20000]);
        $cheese = Ingredient::create(['name' => 'Cheese', 'stock_capacity' => 5000, 'current_stock_amount' => 5000]);
        $onion = Ingredient::create(['name' => 'Onion', 'stock_capacity' => 1000, 'current_stock_amount' => 1000]);

        $burger = Product::create(['name' => 'Burger']);
        $burger->ingredients()->attach([$beef->id => ['ingredient_amount' => 150], $cheese->id => ['ingredient_amount' => 30], $onion->id => ['ingredient_amount' => 20]]);

        $response = $this->postJson('/api/orders', [
            'products' => [
                ['product_id' => $burger->id, 'quantity' => 2],
            ],
        ]);

        $response->assertStatus(200);
        $this->assertDatabaseHas('orders', ['id' => 1]);

        $this->assertEquals(19700, $beef->fresh()->current_stock_amount);
        $this->assertEquals(4940, $cheese->fresh()->current_stock_amount);
        $this->assertEquals(960, $onion->fresh()->current_stock_amount);
    }
}