<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call([
            CurrencySeeder::class,
            TutorSubjectSeeder::class,
            UserSeeder::class,
            TutorReviewSeeder::class,
            PlayerSettingSeeder::class,
            LanguageSeeder::class,
            CountrySeeder::class,
            ContactSeeder::class,
        ]);
    }
}
