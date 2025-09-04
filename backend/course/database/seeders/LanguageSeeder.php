<?php

namespace Database\Seeders;

use App\Models\Language;
use Illuminate\Database\Seeder;

class LanguageSeeder extends Seeder
{
    public function run(): void
    {
        Language::insert([
            ['name' => 'English', 'direction' => 'ltr'],
            ['name' => 'Arabic', 'direction' => 'rtl'],
            ['name' => 'French', 'direction' => 'ltr'],
            ['name' => 'German', 'direction' => 'ltr'],
            ['name' => 'Spanish', 'direction' => 'ltr'],
        ]);
    }
}
