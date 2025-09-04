<?php

namespace Database\Seeders;

use App\Models\TutorSubject;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class TutorSubjectSeeder extends Seeder
{
    public function run(): void
    {
        $subjects = ['Mathematics', 'Science', 'English', 'Physics', 'Chemistry'];

        foreach ($subjects as $name) {
            TutorSubject::insert([
                'name' => $name,
                'slug' => Str::slug($name),
                'status' => true,
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }
    }
}
