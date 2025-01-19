<?php

namespace App\Services;

use App\Models\Ingredient;
use App\Models\ProductOrder;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class StockService
{
    /**
     * Deduct stock for the given product order.
     *
     * @param  ProductOrder  $productOrder
     * @return void
     *
     * @throws ValidationException
     */
    public function deductStockForOrder(ProductOrder $productOrder): void
    {
        DB::transaction(function () use ($productOrder) {
            $productOrder->product->ingredients->each(function ($ingredient) use ($productOrder) {
                $neededAmount = $this->calculateNeededIngredientAmount(
                    $ingredient->pivot->ingredient_amount,
                    $productOrder->quantity
                );

                $this->checkStockAvailability(
                    $ingredient->current_stock_amount,
                    $neededAmount,
                    $ingredient->name
                );

                $this->deductStock($ingredient, $neededAmount);
            });
        });
    }


    /**
     * Check and notify if any ingredient is below 50% stock.
     *
     * @param  ProductOrder  $productOrder
     * @return void
     */
    public function checkAndNotifyLowStock(ProductOrder $productOrder): void
    {
        $productOrder->product->ingredients->each(function ($ingredient) {
            if ($this->isLowStock($ingredient)) {
                $this->notifyUser($ingredient);
            }
        });
    }

    /**
     * Calculate the total amount of ingredient needed for this product order.
     *
     * @param  float|int  $ingredientAmount
     * @param  int        $quantity
     * @return float|int
     */
    private function calculateNeededIngredientAmount($ingredientAmount, int $quantity)
    {
        return $ingredientAmount * $quantity;
    }

    /**
     * Check stock availability for an ingredient; throw ValidationException if insufficient.
     *
     * @param  float|int  $currentStock
     * @param  float|int  $neededAmount
     * @param  string     $ingredientName
     * @return void
     *
     * @throws ValidationException
     */
    private function checkStockAvailability($currentStock, $neededAmount, string $ingredientName): void
    {
        if ($neededAmount > $currentStock) {
            throw ValidationException::withMessages([
                'quantity' => sprintf(
                    'Not enough ingredients in stock. The ingredient %s is currently only %sg. Needed: %sg.',
                    $ingredientName,
                    $currentStock,
                    $neededAmount
                ),
            ]);
        }
    }

    /**
     * Deduct the needed amount from the ingredient's current stock.
     *
     * @param  Ingredient  $ingredient
     * @param  float|int   $neededAmount
     * @return void
     */
    private function deductStock(Ingredient $ingredient, $neededAmount): void
    {
        $ingredient->update([
            'current_stock_amount' => $ingredient->current_stock_amount - $neededAmount,
        ]);
    }

    /**
     * Check if the ingredient is below 50% stock.
     *
     * @param  Ingredient  $ingredient
     * @return bool
     */
    private function isLowStock(Ingredient $ingredient): bool
    {
        return $ingredient->current_stock_amount < ($ingredient->stock_capacity / 2);
    }

    /**
     * Notify the user about low stock and log the event.
     *
     * @param  Ingredient  $ingredient
     * @return void
     */
    private function notifyUser(Ingredient $ingredient): void
    {
        $user = User::first(); // Notify the Admin
        $user->notify(new LowStockNotification($ingredient));

        $ingredient->notificationLogs()->create();
    }
}