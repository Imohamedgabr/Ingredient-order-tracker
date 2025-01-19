<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
use App\Http\Requests\StoreOrderRequest;
use App\Services\StoreOrderService;
use Illuminate\Http\JsonResponse;

class StoreOrderController extends Controller
{
    /**
     * Handle the incoming request.
     *
     * @param  \App\Http\Requests\StoreOrderRequest  $request
     * @param  \App\Services\StoreOrderService       $storeOrderService
     * @return \Illuminate\Http\JsonResponse
     */
    public function __invoke(StoreOrderRequest $request, StoreOrderService $storeOrderService): JsonResponse
    {
        try {
            $validatedData = $request->validated();

            $orderId = $storeOrderService->createOrder($validatedData);

        } catch (\Throwable $th) {
            return response()->json([
                'message'  => $th->getMessage(),
                'order_id' => null,
            ], 422);
        }

        return response()->json([
            'message'  => 'Order created successfully',
            'order_id' => $orderId,
        ], 200);
    }
}
