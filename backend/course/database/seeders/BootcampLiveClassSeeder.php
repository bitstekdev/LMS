<?php

namespace Database\Seeders;

use App\Models\BootcampLiveClass;
use Illuminate\Database\Seeder;

class BootcampLiveClassSeeder extends Seeder
{
    public function run(): void
    {
        BootcampLiveClass::factory()->count(15)->create();
    }
}
