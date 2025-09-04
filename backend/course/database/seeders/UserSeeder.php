<?php

namespace Database\Seeders;

use App\Enums\RoleEnum;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        $now = now();

        User::insert([
            [
                'role' => RoleEnum::Admin->value,
                'name' => 'Admin User',
                'email' => 'admin@example.com',
                'status' => true,
                'phone' => '1234567890',
                'website' => 'https://admin.com',
                'skills' => json_encode(['management', 'system', 'security']),
                'facebook' => 'https://facebook.com/admin',
                'twitter' => 'https://twitter.com/admin',
                'linkedin' => 'https://linkedin.com/in/admin',
                'address' => 'Admin HQ',
                'about' => 'System Administrator',
                'biography' => 'Experienced admin.',
                'educations' => 'MBA',
                'photo' => 'admin.jpg',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'paymentkeys' => json_encode(['stripe' => 'sk_test_...', 'paypal' => 'abc123']),
                'video_url' => 'https://youtube.com/adminintro',
            ],
            [
                'role' => RoleEnum::Instructor->value,
                'name' => 'John Instructor',
                'email' => 'instructor@example.com',
                'status' => true,
                'phone' => '0987654321',
                'website' => 'https://instructor.com',
                'skills' => json_encode(['teaching', 'physics', 'writing']),
                'facebook' => 'https://facebook.com/instructor',
                'twitter' => 'https://twitter.com/instructor',
                'linkedin' => 'https://linkedin.com/in/instructor',
                'address' => 'Teacher City',
                'about' => 'Passionate teacher',
                'biography' => '10 years in academic field',
                'educations' => 'PhD in Physics',
                'photo' => 'instructor.jpg',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'paymentkeys' => json_encode(['stripe' => 'sk_test_abc']),
                'video_url' => 'https://youtube.com/instructorintro',
            ],
            [
                'role' => RoleEnum::Student->value,
                'name' => 'Jane Student',
                'email' => 'student@example.com',
                'status' => true,
                'phone' => '1122334455',
                'website' => 'https://student.com',
                'skills' => json_encode(['research', 'note-taking']),
                'facebook' => 'https://facebook.com/student',
                'twitter' => 'https://twitter.com/student',
                'linkedin' => 'https://linkedin.com/in/student',
                'address' => 'Dorm Lane',
                'about' => 'Computer science student',
                'biography' => '2nd year CS student',
                'educations' => 'BSc in Computer Science (in progress)',
                'photo' => 'student.jpg',
                'email_verified_at' => $now,
                'password' => Hash::make('password'),
                'remember_token' => Str::random(10),
                'paymentkeys' => json_encode(['paypal' => 'xyz987']),
                'video_url' => null,
            ],
        ]);
    }
}
