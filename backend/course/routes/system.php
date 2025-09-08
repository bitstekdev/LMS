<?php

use App\Http\Controllers\CommonController;
use App\Http\Controllers\ModalController;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Route;

// 🔧 Cache Clear Route
Route::get('/clear-cache', function () {
    Artisan::call('cache:clear');
    Artisan::call('config:clear');
    Artisan::call('route:clear');
    Artisan::call('view:clear');
    Cache::flush();

    return 'Application cache cleared';
})->name('cache.clear');

// 🔄 Auth Redirect Logic
Route::get('/dashboard', function () {
    $user = auth('web')->user();

    return match ($user->role) {
        'admin' => redirect()->route('admin.dashboard'),
        'student' => redirect()->route('my.courses'),
        default => redirect()->route('home'),
    };
})->middleware(['auth', 'verified'])->name('dashboard');

// 💬 Common Modal View
Route::get('modal/{view_path}', [ModalController::class, 'common_view_function'])->name('modal');

// 🎥 Video Fetcher
Route::any('get-video-details/{url?}', [CommonController::class, 'get_video_details'])
    ->name('get.video.details');

// 🖼️ Dynamic View Renderer
Route::get('view/{path}', [CommonController::class, 'rendered_view'])->name('view');

// 📱 Mobile Bar Close Redirect
Route::get('closed_back_to_mobile_ber', function () {
    session()->forget('app_url');

    return redirect()->back();
})->name('closed_back_to_mobile_ber');
