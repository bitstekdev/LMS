<?php

namespace Database\Seeders;

use App\Models\CartItem;
use Illuminate\Database\Seeder;

class CartItemSeeder extends Seeder
{
    public function run(): void
    {
        CartItem::factory()->count(10)->create();
    }
}
