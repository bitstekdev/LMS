<?php

namespace Database\Seeders;

use App\Models\BootcampPurchase;
use Illuminate\Database\Seeder;

class BootcampPurchaseSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        BootcampPurchase::factory()->count(10)->create();
    }
}
