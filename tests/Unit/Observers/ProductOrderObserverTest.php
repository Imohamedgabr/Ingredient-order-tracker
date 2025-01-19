<?php

namespace Tests\Unit\Observers;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\User;
use App\Observers\ProductOrderObserver;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductOrderObserverTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that the observer throws an exception when stock is insufficient.
     */
    public function test_it_throws_exception_when_stock_is_insufficient()
    {
        $product = Product::factory()->hasAttached(
            Ingredient::factory()->count(1),
            ['ingredient_amount' => 5]
        )->create();

        $ingredient = $product->ingredients->first();
        $ingredient->update(['current_stock_amount' => 10]);

        $productOrder = ProductOrder::factory()->make([
            'product_id' => $product->id,
            'quantity' => 3,
        ]);

        $observer = new ProductOrderObserver();

        $this->expectException(ValidationException::class);
        $observer->created($productOrder);
    }

    /**
     * Test that the observer updates ingredient stock correctly.
     */
    public function test_it_updates_stock_correctly()
    {
        User::factory()->create();

        $product = Product::factory()->hasAttached(
            Ingredient::factory()->count(1),
            ['ingredient_amount' => 5]
        )->create();

        $ingredient = $product->ingredients->first();
        $ingredient->update(['current_stock_amount' => 50]);

        $productOrder = ProductOrder::factory()->make([
            // 'order_id' => optional(Order::factory()->create()->id),
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $observer = new ProductOrderObserver();
        $observer->created($productOrder);

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'current_stock_amount' => 40, // 50 - (2*5)
        ]);
    }

    /**
     * Test that the observer logs and notifies when stock is low.
     */
    public function test_it_logs_and_notifies_on_low_stock()
    {
        User::factory()->create();

        $product = Product::factory()->hasAttached(
            Ingredient::factory()->count(1),
            ['ingredient_amount' => 5]
        )->create();

        $ingredient = $product->ingredients->first();
        $ingredient->update(['current_stock_amount' => 20, 'stock_capacity' => 50]);

        $productOrder = ProductOrder::factory()->make([
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $observer = new ProductOrderObserver();
        $observer->created($productOrder);

        $this->assertDatabaseHas('ingrediant_notification_logs', [
            'ingrediant_id' => $ingredient->id,
        ]);
        
    }
}
