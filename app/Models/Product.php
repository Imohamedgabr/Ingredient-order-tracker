<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Product extends Model
{
    use HasFactory;

    public function ingredients()
    {
        return $this->belongsToMany(Ingredient::class, 'product_ingredients')
            ->withPivot('ingredient_amount')
            ->using(ProductIngredient::class);
    }

    public function orders()
    {
        return $this->belongsToMany(Order::class, 'product_order')
            ->withPivot('quantity')
            ->using(ProductOrder::class);
    }
}
