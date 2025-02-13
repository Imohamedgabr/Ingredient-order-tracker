<?php

namespace App\Services;

use App\Models\Order;
use Illuminate\Support\Facades\DB;

class StoreOrderService
{
    public function __construct(private Order $order)
    {
    }

    /**
     * Create an order and attach products.
     * 
     * @param  array  $data  (validated request data)
     * @return int           The newly created order's ID
     */
    public function createOrder(array $data): int
    {
        return DB::transaction(function () use ($data) {
            $order = $this->order->create();

            foreach ($data['products'] as $product) {
                $order->products()->attach($product['product_id'], [
                    'quantity' => $product['quantity'],
                ]);
            }

            return $order->id;
        });
    }
}
