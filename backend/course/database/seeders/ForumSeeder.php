<?php

namespace Database\Seeders;

use App\Models\Forum;
use Illuminate\Database\Seeder;

class ForumSeeder extends Seeder
{
    public function run(): void
    {
        Forum::factory(10)->create();
    }
}
