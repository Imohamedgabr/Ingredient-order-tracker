<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Contracts\Validation\Validator;
use Illuminate\Http\Exceptions\HttpResponseException;

class StoreOrderRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'products'                  => ['required', 'array'],
            'products.*.product_id'     => ['required', 'integer', 'exists:products,id'],
            'products.*.quantity'       => ['required', 'integer'],
        ];
    }

    /**
     * We override failedValidation so that our tests pass 
     * with the exact JSON structure:
     *
     * {
     *   "message": "The selected products.0.product_id is invalid.",
     *   "order_id": null
     * }
     */
    protected function failedValidation(Validator $validator)
    {
        // Just grab the first error message
        $errorMessage = $validator->errors()->first();

        throw new HttpResponseException(
            response()->json([
                'message'  => $errorMessage,
                'order_id' => null,
            ], 422)
        );
    }
}
