<?php

namespace Database\Seeders;

use App\Models\Ingredient;
use App\Models\Product;
use App\Models\User;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {
        User::factory()->create([
            'name' => 'Test User',
            'email' => 'test@example.com',
        ]);

        Product::factory()->create([
            'name' => 'Burger Large',
        ]);

        Product::factory()->create([
            'name' => 'Burger Medium',
        ]);

        Product::factory()->create([
            'name' => 'Burger Small',
        ]);

        Ingredient::factory()->create([
            'name' => 'Beef',
            'stock_capacity' => 10000,
        ]);

        Ingredient::factory()->create([
            'name' => 'Onion',
            'stock_capacity' => 10000,
        ]);

        Ingredient::factory()->create([
            'name' => 'Cheese',
            'stock_capacity' => 10000,
        ]);

        Product::each(function ($product) {
            Ingredient::each(function ($ingredient) use ($product) {
                $product->ingredients()->attach($ingredient, [
                    'ingredient_amount' => $product->id * 10,
                ]);
            });
        });
    }
}
