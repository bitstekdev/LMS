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
            // Language and Phrases
            LanguageSeeder::class,
            LanguagePhraseSeeder::class,

            // Payment Gateways & Settings
            PaymentGatewaySeeder::class,
            HomePageSettingSeeder::class,
            SettingSeeder::class,
            PlayerSettingSeeder::class,
            NotificationSettingSeeder::class,

            // Independent
            CurrencySeeder::class,
            CountrySeeder::class,
            BuilderPageSeeder::class,
            CategorySeeder::class,

            // Dependent
            UserSeeder::class,
            ContactSeeder::class,
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

            // Blog Hierarchy
            BlogCategorySeeder::class,
            BlogSeeder::class,
            BlogCommentSeeder::class,
            BlogLikeSeeder::class,
        ]);
    }
}
