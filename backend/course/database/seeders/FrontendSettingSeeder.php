<?php

namespace Database\Seeders;

use App\Models\FrontendSetting;
use Illuminate\Database\Seeder;

class FrontendSettingSeeder extends Seeder
{
    public function run(): void
    {
        $settings = [
            ['key' => 'banner_title', 'value' => 'Start learning from the world’s pro Instructors'],
            ['key' => 'banner_sub_title', 'value' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum.'],
            ['key' => 'about_us', 'value' => '<div>Limitless learning at your fingertips</div><div><br></div><div>Advertising a business online includes assembling the awesome site. Having the most well-planned SEO services keeps you on top.</div>'],
            ['key' => 'terms_and_condition', 'value' => '<h2>Terms and Condition</h2>'],
            ['key' => 'privacy_policy', 'value' => '<h2>Privacy Policy</h2>'],
            ['key' => 'cookie_note', 'value' => 'This website uses cookies to personalize content and analyse traffic in order to offer you a better experience.'],
            ['key' => 'cookie_status', 'value' => '0'],
            ['key' => 'cookie_policy', 'value' => '<h2>Cookie policy</h2><ol><li>Cookies are small text files...</li></ol>'],
            ['key' => 'banner_image', 'value' => 'uploads/banner_image/banner-image.png'],
            ['key' => 'light_logo', 'value' => 'uploads/light_logo/light-logo-default.png'],
            ['key' => 'dark_logo', 'value' => 'uploads/dark_logo/dark-logo-default.png'],
            ['key' => 'small_logo', 'value' => 'uploads/small_logo/small-logo-1712661659.jpg'],
            ['key' => 'favicon', 'value' => 'uploads/favicon/favicon-default.png'],
            ['key' => 'recaptcha_status', 'value' => '0'],
            ['key' => 'recaptcha_secretkey', 'value' => 'Valid-secret-key'],
            ['key' => 'recaptcha_sitekey', 'value' => 'Valid-site-key'],
            ['key' => 'refund_policy', 'value' => '<h2>Refund Policy</h2>'],
            ['key' => 'facebook', 'value' => 'https://facebook.com'],
            ['key' => 'twitter', 'value' => 'https://twitter.com'],
            ['key' => 'linkedin', 'value' => 'https://linkedin.com'],

            // ✅ Proper JSON (arrays encoded before insert)
            ['key' => 'website_faqs', 'value' => json_encode([
                ['question' => 'How to create an account?', 'answer' => 'Interactively procrastinate high-payoff content...'],
                ['question' => 'Do you provide any support for this kit?', 'answer' => 'Quickly cultivate optimal processes...'],
                ['question' => 'How long do you provide support?', 'answer' => 'Completely iterate covalent strategic...'],
            ])],

            ['key' => 'motivational_speech', 'value' => json_encode([
                ['title' => 'Jenny Murtagh', 'designation' => 'Graphic Design', 'description' => 'Lorem Ipsum available...', 'image' => 'I6zvV1Mr30YUhLfJgwje.png'],
                ['title' => 'Jenny Murtagh', 'designation' => 'Graphic Design', 'description' => 'Lorem Ipsum available...', 'image' => 'ZLfkhGame7sYQvqKxD0J.png'],
                ['title' => 'Jenny Murtagh', 'designation' => 'Graphic Design', 'description' => 'Lorem Ipsum available...', 'image' => 'xBYkXnfvmPiU3j0CzME1.png'],
            ])],

            ['key' => 'home_page', 'value' => null],

            ['key' => 'contact_info', 'value' => json_encode([
                'email' => 'creativeitem@example.com',
                'phone' => '67564345676',
                'address' => '629 12th St, Modesto',
                'office_hours' => '8',
                'location' => '40.689880, -74.045203',
            ])],

            ['key' => 'promo_video_provider', 'value' => 'youtube'],
            ['key' => 'promo_video_link', 'value' => 'https://youtu.be/4QCaXTOwigw?si=NsFeBQhWNZC859-l'],
            ['key' => 'mobile_app_link', 'value' => 'https://youtu.be/4QCaXTOwigw?si=NsFeBQhWNZC859-l'],
        ];

        FrontendSetting::insert($settings);
    }
}
