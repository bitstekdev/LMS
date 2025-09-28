<?php

use App\Http\Controllers\CommonController;
use App\Http\Controllers\ModalController;
use Illuminate\Support\Facades\Route;

// 🔄 Auth Redirect Logic
Route::get('/dashboard', function () {
    $user = auth('web')->user();

    return match ($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'instructor' => redirect()->route('instructor.dashboard'),
        'student' => redirect()->route('my.courses'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// 💬 Common Modal View
Route::get('modal/{view_path}', [ModalController::class, 'common_view_function'])->name('modal');

// 🎥 Video Fetcher
Route::any('get-video-details/{url?}', [CommonController::class, 'get_video_details'])
    ->name('get.video.details');

// 🖼️ Dynamic View Renderer
Route::get('view/{path}', [CommonController::class, 'rendered_view'])->name('view');
