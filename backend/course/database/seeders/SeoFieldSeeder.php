<?php

namespace Database\Seeders;

use App\Models\SeoField;
use Illuminate\Database\Seeder;

class SeoFieldSeeder extends Seeder
{
    public function run(): void
    {
        $records = [
            [
                'course_id' => null,
                'blog_id' => null,
                'bootcamp_id' => null,
                'route' => 'Home',
                'name_route' => 'home',
                'meta_title' => 'Home page',
                'meta_keywords' => null,
                'meta_description' => 'Home page for academy Seo',
                'meta_robot' => 'xxxxxx',
                'canonical_url' => 'https://academy.com',
                'custom_url' => 'https://academy.com',
                'json_ld' => '<script type="application/ld+json">{ "@context":"http://schema.org", "@type":"WebSite", "name":"CodeCanyon", "url":"https://codecanyon.net" }</script>',
                'og_title' => 'ooooooooo',
                'og_description' => 'zzzzzzzzzz',
                'og_image' => 'OG-home.jpg',
            ],
            [
                'course_id' => null,
                'blog_id' => null,
                'bootcamp_id' => null,
                'route' => 'Compare',
                'name_route' => 'compare',
                'meta_title' => 'Course compare',
                'meta_keywords' => '[{"value":"course"},{"value":"compare"},{"value":"difference"}]',
                'meta_description' => 'Course compare',
                'meta_robot' => 'xxxxxx',
                'canonical_url' => 'https:://academy.com/course-compare',
                'custom_url' => 'https:://academy.com/course-compare',
                'json_ld' => null,
                'og_title' => 'Course compare',
                'og_description' => 'Course compare',
                'og_image' => '2-customer-php-version.PNG',
            ],
            [
                'course_id' => null,
                'blog_id' => null,
                'bootcamp_id' => null,
                'route' => 'Privacy',
                'name_route' => 'privacy.policy',
                'meta_title' => null,
                'meta_keywords' => null,
                'meta_description' => null,
                'meta_robot' => null,
                'canonical_url' => null,
                'custom_url' => null,
                'json_ld' => null,
                'og_title' => null,
                'og_description' => null,
                'og_image' => 'OG-documantation.jpg',
            ],
            [
                'course_id' => null,
                'blog_id' => null,
                'bootcamp_id' => null,
                'route' => 'Refund',
                'name_route' => 'refund.policy',
                'meta_title' => null,
                'meta_keywords' => null,
                'meta_description' => null,
                'meta_robot' => null,
                'canonical_url' => null,
                'custom_url' => null,
                'json_ld' => null,
                'og_title' => null,
                'og_description' => null,
                'og_image' => 'OG-Blog.jpg',
            ],
            [
                'course_id' => null,
                'blog_id' => null,
                'bootcamp_id' => null,
                'route' => 'Terms- condition',
                'name_route' => 'terms.condition',
                'meta_title' => null,
                'meta_keywords' => null,
                'meta_description' => null,
                'meta_robot' => null,
                'canonical_url' => null,
                'custom_url' => null,
                'json_ld' => null,
                'og_title' => null,
                'og_description' => null,
                'og_image' => 'OG-service.jpg',
            ],
            [
                'course_id' => null,
                'blog_id' => null,
                'bootcamp_id' => null,
                'route' => 'Faq',
                'name_route' => 'faq',
                'meta_title' => 'Creative elements - ui subscription system',
                'meta_keywords' => '[{"value":"ui kits"},{"value":"website template"},{"value":"video template"}]',
                'meta_description' => 'Best and affordable ui kit subscription system',
                'meta_robot' => null,
                'canonical_url' => null,
                'custom_url' => null,
                'json_ld' => null,
                'og_title' => null,
                'og_description' => null,
                'og_image' => 'OG-elements home.jpg',
            ],
            [
                'course_id' => null,
                'blog_id' => null,
                'bootcamp_id' => null,
                'route' => 'Cookie policy',
                'name_route' => 'cookie.policy',
                'meta_title' => 'Academy LMS - Cookie policy',
                'meta_keywords' => '[{"value":"ui kits"},{"value":"website template"},{"value":"video template"}]',
                'meta_description' => 'NULL',
                'meta_robot' => null,
                'canonical_url' => null,
                'custom_url' => null,
                'json_ld' => null,
                'og_title' => null,
                'og_description' => null,
                'og_image' => 'OG-elements home.jpg',
            ],
        ];

        foreach ($records as $row) {
            SeoField::updateOrCreate(
                ['name_route' => $row['name_route']],
                $row
            );
        }
    }
}
