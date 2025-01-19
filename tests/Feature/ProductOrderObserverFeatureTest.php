<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

class ProductOrderObserverFeatureTest extends TestCase
{
    use RefreshDatabase;

    /**
     * Test that stock is updated when an order is created.
     *
     * @return void
     */
    public function test_it_updates_stock_when_order_is_created()
    {
        User::factory()->create();

        $product = Product::factory()->hasAttached(
            Ingredient::factory()->count(1),
            ['ingredient_amount' => 5]
        )->create();

        $ingredient = $product->ingredients->first();
        $ingredient->update(['current_stock_amount' => 50]);

        $order = Order::factory()->create();

        ProductOrder::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $this->assertDatabaseHas('ingredients', [
            'id' => $ingredient->id,
            'current_stock_amount' => 40, // 50 - (2 * 5)
        ]);
    }

    /**
     * Test that a validation exception is thrown for insufficient stock.
     *
     * @return void
     */
    public function test_it_throws_validation_exception_for_insufficient_stock()
    {
        $this->expectException(ValidationException::class);

        User::factory()->create();

        $product = Product::factory()->hasAttached(
            Ingredient::factory()->count(1),
            ['ingredient_amount' => 5]
        )->create();

        $ingredient = $product->ingredients->first();
        $ingredient->update(['current_stock_amount' => 10]);

        $order = Order::factory()->create();

        ProductOrder::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);
    }

    /**
     * Test that a low stock notification is sent and logged when stock falls below 50%.
     *
     * @return void
     */
    public function test_it_logs_and_notifies_on_low_stock()
    {
        Notification::fake();

        User::factory()->create();

        $product = Product::factory()->hasAttached(
            Ingredient::factory()->count(1),
            ['ingredient_amount' => 5]
        )->create();

        $ingredient = $product->ingredients->first();
        $ingredient->update([
            'current_stock_amount' => 20,
            'stock_capacity' => 50,
        ]);

        $order = Order::factory()->create();

        ProductOrder::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        Notification::assertSentTo(
            User::first(),
            LowStockNotification::class
        );

        $this->assertDatabaseHas('ingredient_notification_logs', [
            'ingredient_id' => $ingredient->id,
        ]);
    }
}