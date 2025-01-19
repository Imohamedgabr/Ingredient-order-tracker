<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class IngrediantNotificationLog extends Model
{
    use HasFactory;

    protected $fillable = [
        'ingrediant_id'
    ];
}
