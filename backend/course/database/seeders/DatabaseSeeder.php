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
            // Independent
            CurrencySeeder::class,
            LanguageSeeder::class,
            CountrySeeder::class,
            PlayerSettingSeeder::class,
            HomePageSettingSeeder::class,
            BuilderPageSeeder::class,
            CategorySeeder::class,

            // Dependent
            UserSeeder::class,
            ContactSeeder::class,

            // Needs more than one
            NotificationSettingSeeder::class,
            UserReviewSeeder::class,

            // Bootcamp Hierarchy
            BootcampCategorySeeder::class,
            BootcampSeeder::class,
            BootcampLiveClassSeeder::class,
            BootcampModuleSeeder::class,
            BootcampResourceSeeder::class,

            // Tutor Hierarchy
            TutorCategorySeeder::class,
            TutorSubjectSeeder::class,
            TutorScheduleSeeder::class,
            TutorBookingSeeder::class,
            TutorCanTeachSeeder::class,
            TutorReviewSeeder::class,
        ]);
    }
}
