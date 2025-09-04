<?php

namespace Database\Seeders;

use App\Models\PlayerSetting;
use Illuminate\Database\Seeder;

class PlayerSettingSeeder extends Seeder
{
    public function run(): void
    {
        PlayerSetting::insert([
            ['title' => 'watermark_width', 'description' => '100'],
            ['title' => 'watermark_height', 'description' => '24'],
            ['title' => 'watermark_top', 'description' => '10'],
            ['title' => 'watermark_left', 'description' => '10'],
            ['title' => 'watermark_opacity', 'description' => '1'],
            ['title' => 'watermark_type', 'description' => 'image'],
            ['title' => 'watermark_logo', 'description' => 'uploads/watermark.png'],
            ['title' => 'animation_speed', 'description' => 'normal'],
        ]);
    }
}
