<?php

namespace Tests\Unit\Services;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Models\User;
use Illuminate\Validation\ValidationException;
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
        // Create a user (admin for notifications)
        User::factory()->create();
        $this->stockService = new StockService();
    }

    public function test_deduct_stock_for_order()
    {
        $ingredient = Ingredient::factory()->create([
            'current_stock_amount' => 400,
        ]);

        $product = Product::factory()->create();
        $product->ingredients()->detach();
        $product->ingredients()->attach($ingredient, ['ingredient_amount' => 100]);
        $product->refresh();

        $productOrder = ProductOrder::factory()->create([
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $result = $this->stockService->deductStockForOrder($productOrder);

        $this->assertNull($result);
        $this->assertEquals(200, $ingredient->fresh()->current_stock_amount);
    }

    public function test_deduct_stock_for_order_throws_exception_if_stock_insufficient()
    {
        $this->expectException(ValidationException::class);

        $ingredient = Ingredient::factory()->create([
            'current_stock_amount' => 50,
        ]);

        $product = Product::factory()->create();
        $product->ingredients()->detach();
        $product->ingredients()->attach($ingredient, ['ingredient_amount' => 100]);
        $product->refresh();

        $productOrder = ProductOrder::factory()->create([
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->stockService->deductStockForOrder($productOrder);
    }

    public function test_no_notification_when_stock_is_exactly_50_percent()
    {
        Notification::fake();

        $ingredient = Ingredient::factory()->create([
            'current_stock_amount' => 100,
            'stock_capacity'       => 200,
        ]);

        $product = Product::factory()->create();
        $product->ingredients()->detach();
        $product->ingredients()->attach($ingredient, ['ingredient_amount' => 0]);
        $product->refresh();

        $productOrder = ProductOrder::factory()->create([
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->stockService->checkAndNotifyLowStock($productOrder);

        Notification::assertNothingSent();
    }

    public function test_deduct_stock_for_order_with_multiple_ingredients()
    {
        $ingredient1 = Ingredient::factory()->create([
            'current_stock_amount' => 800,
        ]);
        $ingredient2 = Ingredient::factory()->create([
            'current_stock_amount' => 900,
        ]);

        $product = Product::factory()->create();
        $product->ingredients()->detach();
        $product->ingredients()->attach([
            $ingredient1->id => ['ingredient_amount' => 200],
            $ingredient2->id => ['ingredient_amount' => 150],
        ]);
        $product->refresh();

        $productOrder = ProductOrder::factory()->create([
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $this->stockService->deductStockForOrder($productOrder);
        $this->assertEquals(0, $ingredient1->fresh()->current_stock_amount);
        $this->assertEquals(300, $ingredient2->fresh()->current_stock_amount);
    }

    public function test_deduct_stock_for_order_with_zero_stock_throws_exception()
    {
        $this->expectException(ValidationException::class);

        $ingredient = Ingredient::factory()->create([
            'current_stock_amount' => 0,
        ]);

        $product = Product::factory()->create();
        $product->ingredients()->detach();
        $product->ingredients()->attach($ingredient, ['ingredient_amount' => 100]);
        $product->refresh();

        $productOrder = ProductOrder::factory()->create([
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $this->stockService->deductStockForOrder($productOrder);
    }

    public function test_deduct_stock_for_order_with_no_ingredients()
    {
        $product = Product::factory()->create();
        $product->ingredients()->detach();

        $productOrder = ProductOrder::factory()->create([
            'product_id' => $product->id,
            'quantity'   => 1,
        ]);

        $result = $this->stockService->deductStockForOrder($productOrder);

        $this->assertNull($result);
    }
}
