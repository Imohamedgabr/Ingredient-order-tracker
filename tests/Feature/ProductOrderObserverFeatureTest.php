<?php

namespace Tests\Feature;

use App\Models\Ingredient;
use App\Models\IngrediantNotificationLog;
use App\Models\Order;         // <-- ADD THIS
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

    public function test_it_logs_and_notifies_on_low_stock()
    {
        Notification::fake();

        User::factory()->create();

        $product = Product::factory()->hasAttached(
            Ingredient::factory()->count(1),
            ['ingredient_amount' => 5]
        )->create();

        $ingredient = $product->ingredients->first();
        $ingredient->update(['current_stock_amount' => 20, 'stock_capacity' => 50]);

        $order = Order::factory()->create();

        ProductOrder::factory()->create([
            'order_id'   => $order->id,
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        Notification::assertSentTo(User::first(), LowStockNotification::class);

        $this->assertDatabaseHas('ingrediant_notification_logs', [
            'ingrediant_id' => $ingredient->id,
        ]);
    }
}
