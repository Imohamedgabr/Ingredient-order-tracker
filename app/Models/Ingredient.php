<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Ingredient extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'stock_capacity',
        'current_stock_amount',
    ];

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_ingredients');
    }
}
