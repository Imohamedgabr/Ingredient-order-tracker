<?php

namespace Tests\Unit\Services;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\User;
use App\Notifications\LowStockNotification;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class StockServiceTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stockService;

    protected function setUp(): void
    {
        parent::setUp();

        User::factory()->create();

        $this->stockService = new StockService();
    }

    /**
     * Test that stock is deducted correctly for an order.
     *
     * @return void
     */
    public function test_deduct_stock_for_order()
    {
        $ingredient = Ingredient::factory()->create(['current_stock_amount' => 400]);

        $product = Product::factory()->create();
        $product->ingredients()->attach($ingredient, ['ingredient_amount' => 100]);

        $productOrder = ProductOrder::factory()->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->stockService->deductStockForOrder($productOrder);

        $this->assertEquals(200, $ingredient->fresh()->current_stock_amount);
    }

    /**
     * Test that a low stock notification is sent when stock falls below 50%.
     *
     * @return void
     */
    public function test_low_stock_notification()
    {
        Notification::fake();

        $ingredient = Ingredient::factory()->create([
            'current_stock_amount' => 100,
            'stock_capacity' => 200,
        ]);

        $product = Product::factory()->create();
        $product->ingredients()->attach($ingredient, ['ingredient_amount' => 100]);

        $productOrder = ProductOrder::factory()->create([
            'product_id' => $product->id,
            'quantity' => 1,
        ]);

        $this->stockService->checkAndNotifyLowStock($productOrder);

        Notification::assertSentTo(
            User::first(),
            LowStockNotification::class
        );

        $this->assertDatabaseHas('ingredient_notification_logs', [
            'ingredient_id' => $ingredient->id,
        ]);
    }
}