<?php

namespace Database\Seeders;

use App\Models\BuilderPage;
use Illuminate\Database\Seeder;

class BuilderPageSeeder extends Seeder
{
    public function run(): void
    {
        $pages = [
            [
                'name' => 'Elegant',
                'html' => '',
                'identifier' => 'elegant',
                'is_permanent' => 1,
                'status' => 0,
                'edit_home_id' => null,
            ],
            [
                'name' => 'Kindergarden',
                'html' => null,
                'identifier' => 'kindergarden',
                'is_permanent' => 1,
                'status' => 0,
                'edit_home_id' => 1,
            ],
            [
                'name' => 'Cooking',
                'html' => null,
                'identifier' => 'cooking',
                'is_permanent' => 1,
                'status' => 0,
                'edit_home_id' => 1,
            ],
            [
                'name' => 'University',
                'html' => null,
                'identifier' => 'university',
                'is_permanent' => 1,
                'status' => 0,
                'edit_home_id' => 1,
            ],
            [
                'name' => 'Language',
                'html' => null,
                'identifier' => 'language',
                'is_permanent' => 1,
                'status' => 0,
                'edit_home_id' => null,
            ],
            [
                'name' => 'Development',
                'html' => null,
                'identifier' => 'development',
                'is_permanent' => 1,
                'status' => 0,
                'edit_home_id' => 1,
            ],
            [
                'name' => 'Marketplace',
                'html' => null,
                'identifier' => 'marketplace',
                'is_permanent' => 1,
                'status' => 0,
                'edit_home_id' => 1,
            ],
            [
                'name' => 'Meditation',
                'html' => null,
                'identifier' => 'meditation',
                'is_permanent' => 1,
                'status' => 0,
                'edit_home_id' => 1,
            ],
            [
                'name' => 'Default',
                'html' => '["top_bar","header","hero_banner","features","category","featured_courses","about_us","testimonial","blog","footer"]',
                'identifier' => null,
                'is_permanent' => null,
                'status' => 1,
                'edit_home_id' => null,
            ],
        ];

        BuilderPage::insert($pages);
    }
}
