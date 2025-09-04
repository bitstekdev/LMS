<?php

namespace Database\Seeders;

use App\Models\BootcampModule;
use Illuminate\Database\Seeder;

class BootcampModuleSeeder extends Seeder
{
    public function run(): void
    {
        BootcampModule::factory()->count(20)->create();
    }
}
