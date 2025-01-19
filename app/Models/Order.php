<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

class Order extends Model
{
    use HasFactory;

    public function products()
    {
        return $this->belongsToMany(Product::class, 'product_order')->using(ProductOrder::class);
    }

    public function deleteWithRelations()
    {
        DB::table('product_order')->where('order_id', $this->id)->delete();
        $this->delete();
    }
}
