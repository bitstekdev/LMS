<?php

namespace Database\Seeders;

use App\Models\FrontendSetting;
use Illuminate\Database\Seeder;

class FrontendSettingSeeder extends Seeder
{
    public function run(): void
    {
        FrontendSetting::insert([
            ['key' => 'banner_title', 'value' => 'Start learning from the world’s pro Instructors'],
            ['key' => 'banner_sub_title', 'value' => 'It is a long established fact that a reader will be distracted by the readable content of a page when looking at its layout. The point of using Lorem Ipsum.'],
            ['key' => 'about_us', 'value' => '<div>Limitless learning at your fingertips<\/div><div><br><\/div><div>Limitless learning at your fingertipsAdvertising a busines online includes assembling the they awesome site. Having the most well-planned on to the our SEO services keep you on the top a business Having the moston to the online.<\/div><div><br><\/div><div><br><\/div><div><br><\/div><div>Advertising a busines online includes assembling the they awesome site.<\/div><div><br><\/div><div><br><\/div><div>Range including technical skills<\/div><div>Range including technical skills<\/div><div>Range including technical skills<\/div><div><br><\/div>'],
            ['key' => 'terms_and_condition', 'value' => '<h2>Terms and Condition<\/h2>'],
            ['key' => 'privacy_policy', 'value' => '<p><\/p><p><\/p><h2><span xss="removed">Privacy Policy<\/span><\/h2>'],
            ['key' => 'theme', 'value' => 'default'],
            ['key' => 'cookie_note', 'value' => 'This website uses cookies to personalize content and analyse traffic in order to offer you a better experience.'],
            ['key' => 'cookie_status', 'value' => '0'],
            ['key' => 'cookie_policy', 'value' => '<h2 class="">Cookie policy<\/h2><ol><li>Cookies are small text files that can be used by websites to make a user\'s experience more efficient.<\/li><li>The law states that we can store cookies on your device if they are strictly necessary for the operation of this site. For all other types of cookies we need your permission.<\/li><li>This site uses different types of cookies. Some cookies are placed by third party services that appear on our pages.<\/li><\/ol>'],
            ['key' => 'banner_image', 'value' => 'uploads/banner_image/banner-image.png'],
            ['key' => 'light_logo', 'value' => 'uploads/light_logo/light-logo-default.png'],
            ['key' => 'dark_logo', 'value' => 'uploads/dark_logo/dark-logo-default.png'],
            ['key' => 'small_logo', 'value' => 'uploads/small_logo/small-logo-1712661659.jpg'],
            ['key' => 'favicon', 'value' => 'uploads/favicon/favicon-default.png'],
            ['key' => 'recaptcha_status', 'value' => '0'],
            ['key' => 'recaptcha_secretkey', 'value' => 'Valid-secret-key'],
            ['key' => 'recaptcha_sitekey', 'value' => 'Valid-site-key'],
            ['key' => 'refund_policy', 'value' => '<h2><span xss="removed">Refund Policy<\/span><\/h2>'],
            ['key' => 'facebook', 'value' => 'https:\/\/facebook.com'],
            ['key' => 'twitter', 'value' => 'https:\/\/twitter.com'],
            ['key' => 'linkedin', 'value' => 'https:\/\/linkedin.com'],
            ['key' => 'blog_page_title', 'value' => 'Where possibilities begin'],
            ['key' => 'blog_page_subtitle', 'value' => 'We’re a leading marketplace platform for learning and teaching online. Explore some of our most popular content and learn something new.'],
            ['key' => 'blog_page_banner', 'value' => 'blog-page.png'],
            ['key' => 'instructors_blog_permission', 'value' => '1'],
            ['key' => 'blog_visibility_on_the_home_page', 'value' => '1'],
            ['key' => 'website_faqs', 'value' => '[{\"question\":\"How to create an account?\",\"answer\":\"Interactively procrastinate high-payoff content without backward-compatible data. Quickly to cultivate optimal processes and tactical architectures. For The Completely iterate covalent strategic.\"},{\"question\":\"Do you provide any support for this kit?\",\"answer\":\"Interactively procrastinate high-payoff content without backward-compatible data. Quickly to cultivate optimal processes and tactical architectures. For The Completely iterate covalent strategic.\"},{\"question\":\"How to create an account?\",\"answer\":\"Interactively procrastinate high-payoff content without backward-compatible data. Quickly to cultivate optimal processes and tactical architectures. For The Completely iterate covalent strategic.\"},{\"question\":\"How long do you provide support?\",\"answer\":\"Interactively procrastinate high-payoff content without backward-compatible data. Quickly to cultivate optimal processes and tactical architectures. For The Completely iterate covalent strategic.\"}]'],
            ['key' => 'motivational_speech', 'value' => '[{\"title\":\"Jenny Murtagh\",\"designation\":\"Graphic Design\",\"description\":\"Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even for slightly believable randomised words.\",\"image\":\"I6zvV1Mr30YUhLfJgwje.png\"},{\"title\":\"Jenny Murtagh\",\"designation\":\"Graphic Design\",\"description\":\"Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even for slightly believable randomised words.\",\"image\":\"ZLfkhGame7sYQvqKxD0J.png\"},{\"title\":\"Jenny Murtagh\",\"designation\":\"Graphic Design\",\"description\":\"Lorem Ipsum available, but the majority have suffered alteration in some form, by injected humour, or randomised words which don\'t look even for slightly believable randomised words.\",\"image\":\"xBYkXnfvmPiU3j0CzME1.png\"}]'],
            ['key' => 'home_page', 'value' => null],
            ['key' => 'contact_info', 'value' => '{\"email\":\"creativeitem@example.com\",\"phone\":\"67564345676\",\"address\":\"629 12th St, Modesto\",\"office_hours\":\"8\",\"location\":\"40.689880, -74.045203\"}'],
            ['key' => 'promo_video_provider', 'value' => 'youtube'],
            ['key' => 'promo_video_link', 'value' => 'https:\/\/youtu.be\/4QCaXTOwigw?si=NsFeBQhWNZC859-l'],
            ['key' => 'mobile_app_link', 'value' => 'https:\/\/youtu.be\/4QCaXTOwigw?si=NsFeBQhWNZC859-l'],
        ]);
    }
}
