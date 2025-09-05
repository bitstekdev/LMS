<?php

namespace Database\Seeders;

use App\Models\AddToCart;
use Illuminate\Database\Seeder;

class AddToCartSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        AddToCart::factory()->count(10)->create();
    }
}
