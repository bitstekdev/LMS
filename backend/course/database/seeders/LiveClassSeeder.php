<?php

namespace Database\Seeders;

use App\Models\LiveClass;
use Illuminate\Database\Seeder;

class LiveClassSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        LiveClass::factory()->count(10)->create();
    }
}
