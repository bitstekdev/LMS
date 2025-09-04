<?php

namespace Database\Seeders;

use App\Models\BootcampResource;
use Illuminate\Database\Seeder;

class BootcampResourceSeeder extends Seeder
{
    public function run(): void
    {
        BootcampResource::factory()->count(50)->create();
    }
}
