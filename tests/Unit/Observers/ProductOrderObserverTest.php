<?php

namespace Tests\Unit\Observers;

use App\Models\Ingredient;
use App\Models\Order;
use App\Models\Product;
use App\Models\ProductOrder;
use App\Observers\ProductOrderObserver;
use App\Services\StockService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Mockery;
use Tests\TestCase;

class ProductOrderObserverTest extends TestCase
{
    use RefreshDatabase;

    private StockService $stockService;
    private ProductOrderObserver $observer;

    protected function setUp(): void
    {
        parent::setUp();

        $this->stockService = Mockery::mock(StockService::class);

        $this->observer = new ProductOrderObserver($this->stockService);
    }

    public function test_it_updates_stock_correctly()
    {
        $product = Product::factory()->hasAttached(
            Ingredient::factory()->count(1),
            ['ingredient_amount' => 5]
        )->create();

        $productOrder = ProductOrder::factory()->create([
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $this->stockService
            ->shouldReceive('deductStockForOrder')
            ->with($productOrder)
            ->once();

        $this->stockService
            ->shouldReceive('checkAndNotifyLowStock')
            ->with($productOrder)
            ->once();

        $this->observer->created($productOrder);
    }

    public function test_it_throws_exception_when_stock_is_insufficient()
    {
        $product = Product::factory()->hasAttached(
            Ingredient::factory()->count(1),
            ['ingredient_amount' => 5]
        )->create();

        $productOrder = ProductOrder::factory()->create([
            'product_id' => $product->id,
            'quantity'   => 3,
        ]);

        $exception = ValidationException::withMessages([
            'quantity' => 'Not enough stock for the ingredient.',
        ]);

        $this->stockService
            ->shouldReceive('deductStockForOrder')
            ->with($productOrder)
            ->andThrow($exception);

        $this->expectException(ValidationException::class);

        $this->observer->created($productOrder);
    }

    public function test_it_logs_and_notifies_on_low_stock()
    {
        $product = Product::factory()->hasAttached(
            Ingredient::factory()->count(1),
            ['ingredient_amount' => 5]
        )->create();

        $productOrder = ProductOrder::factory()->create([
            'product_id' => $product->id,
            'quantity'   => 2,
        ]);

        $this->stockService
            ->shouldReceive('deductStockForOrder')
            ->with($productOrder)
            ->once();

        $this->stockService
            ->shouldReceive('checkAndNotifyLowStock')
            ->with($productOrder)
            ->once();

        $this->observer->created($productOrder);
    }
}