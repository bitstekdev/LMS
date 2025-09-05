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
            /*
            |--------------------------------------------------------------------------
            | Core Setup
            |--------------------------------------------------------------------------
            */
            LanguageSeeder::class,
            LanguagePhraseSeeder::class,
            CurrencySeeder::class,
            CountrySeeder::class,

            /*
            |--------------------------------------------------------------------------
            | System Settings
            |--------------------------------------------------------------------------
            */
            PaymentGatewaySeeder::class,
            HomePageSettingSeeder::class,
            SettingSeeder::class,
            PlayerSettingSeeder::class,
            NotificationSettingSeeder::class,

            /*
            |--------------------------------------------------------------------------
            | Independent Content
            |--------------------------------------------------------------------------
            */
            BuilderPageSeeder::class,
            CategorySeeder::class,

            /*
            |--------------------------------------------------------------------------
            | Users & Related
            |--------------------------------------------------------------------------
            */
            UserSeeder::class,
            ContactSeeder::class,
            UserReviewSeeder::class,

            /*
            |--------------------------------------------------------------------------
            | Bootcamp Hierarchy
            |--------------------------------------------------------------------------
            */
            BootcampCategorySeeder::class,
            BootcampSeeder::class,
            BootcampModuleSeeder::class,
            BootcampResourceSeeder::class,
            BootcampLiveClassSeeder::class,

            /*
            |--------------------------------------------------------------------------
            | Tutor Hierarchy
            |--------------------------------------------------------------------------
            */
            TutorCategorySeeder::class,
            TutorSubjectSeeder::class,
            TutorScheduleSeeder::class,
            TutorBookingSeeder::class,
            TutorCanTeachSeeder::class,
            TutorReviewSeeder::class,

            /*
            |--------------------------------------------------------------------------
            | Blog Hierarchy
            |--------------------------------------------------------------------------
            */
            BlogCategorySeeder::class,
            BlogSeeder::class,
            BlogCommentSeeder::class,
            BlogLikeSeeder::class,

            /*
            |--------------------------------------------------------------------------
            | Course Hierarchy
            |--------------------------------------------------------------------------
            */
            CourseSeeder::class,
            SectionSeeder::class,
            LessonSeeder::class,
            ForumSeeder::class,
            MessageSeeder::class,
            WatchHistorySeeder::class,
            CertificateSeeder::class,
            AddToCartSeeder::class,
            CartItemSeeder::class,
            WishlistSeeder::class,
            EnrollmentSeeder::class,
            ReviewSeeder::class,
            LikeDislikeReviewSeeder::class,
            InstructorReviewSeeder::class,
            PaymentHistorySeeder::class,

            /*
            |--------------------------------------------------------------------------
            | Quiz Hierarchy
            |--------------------------------------------------------------------------
            */
            QuizSeeder::class,
            QuizSubmissionSeeder::class,

            /*
            |--------------------------------------------------------------------------
            | Team Training Packages
            |--------------------------------------------------------------------------
            */
            TeamTrainingPackageSeeder::class,
            TeamPackageMemberSeeder::class,
            TeamPackagePurchaseSeeder::class,

            /*
            |--------------------------------------------------------------------------
            | SEO & Final
            |--------------------------------------------------------------------------
            */
            SeoFieldSeeder::class,
        ]);
    }
}
