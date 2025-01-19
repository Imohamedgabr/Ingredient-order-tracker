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

    public function messages()
    {
        return [
            'products.*.product_id.exists' => 'The selected product is invalid.',
            'products.*.quantity.min'      => 'The quantity must be at least 1.',
        ];
    }

    protected function failedValidation(Validator $validator)
    {
        $errorMessage = $validator->errors()->first();

        throw new HttpResponseException(
            response()->json([
                'message'  => $errorMessage,
                'order_id' => null,
            ], 422)
        );
    }
}
