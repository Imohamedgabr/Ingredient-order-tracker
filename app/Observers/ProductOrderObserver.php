<?php

namespace App\Observers;

use App\Models\ProductOrder;
use App\Services\StockService;

class ProductOrderObserver
{
    private StockService $stockService;

    public function __construct(StockService $stockService)
    {
        $this->stockService = $stockService;
    }

    /**
     * Handle the ProductOrder "created" event.
     *
     * @param  ProductOrder  $productOrder
     * @return void
     */
    public function created(ProductOrder $productOrder): void
    {
        $this->stockService->deductStockForOrder($productOrder);
        $this->stockService->checkAndNotifyLowStock($productOrder);
    }
}