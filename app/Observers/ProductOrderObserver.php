<?php

namespace App\Observers;

use App\Models\IngrediantNotificationLog;
use App\Models\ProductOrder;
use App\Models\User;
use App\Notifications\LowStockNotification;
use Illuminate\Validation\ValidationException;

class ProductOrderObserver
{    
    /**
     * created
     *
     * @param  ProductOrder $productOrder
     * @return void
     */
    public function created(ProductOrder $productOrder): void
    {
        $productOrder->product->ingredients->each(function ($ingredient) use ($productOrder) {
            $neededIngredientAmount = $productOrder->quantity * $ingredient->pivot->ingredient_amount;
            if ($neededIngredientAmount > $ingredient->current_stock_amount) {
                throw ValidationException::withMessages([
                    'quantity' => 'Not enough ingredients in stock, '.
                        'The ingredient '.$ingredient->name.' is currently in stock only '.$ingredient->current_stock_amount.'g,'.
                        'The needed amount is '.$neededIngredientAmount.'g.',
                ]);
            }

            $ingredient->update([
                'current_stock_amount' => $ingredient->current_stock_amount - $productOrder->quantity * $ingredient->pivot->ingredient_amount,
            ]);
            // if the ingredient is low on stock, notify the user
            if ($ingredient->current_stock_amount < ($ingredient->stock_capacity / 2)) {
                $lastIngNotTime = IngrediantNotificationLog::where('ingrediant_id', $ingredient->id)->first();
                
                if($lastIngNotTime == null){
                    $this->notifyUser($ingredient);
                }
            }
        });
    }
    
    /**
     * notifyUser
     *
     * @param  mixed $ingredient
     * @return void
     */
    public function notifyUser($ingredient): void
    {
        User::first()->notify(new LowStockNotification($ingredient));
        IngrediantNotificationLog::create(['ingrediant_id'=> $ingredient->id]);
    }
}
