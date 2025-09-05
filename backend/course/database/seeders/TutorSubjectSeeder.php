<?php

namespace Database\Seeders;

use App\Models\TutorSubject;
use Illuminate\Database\Seeder;

class TutorSubjectSeeder extends Seeder
{
    public function run(): void
    {
        TutorSubject::factory()->count(10)->create();
    }
}
