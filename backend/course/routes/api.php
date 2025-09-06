<?php

use App\Http\Controllers\Api\AuthController;
use App\Http\Controllers\Api\CartController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\PaymentController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\ZoomController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
| All routes here are grouped under the "api" middleware group.
| Authenticated routes use Sanctum for token-based auth.
*/

// 👤 Authenticated user fetch
Route::middleware('auth:sanctum')->get('/user', function (Request $request) {
    return $request->user();
});

// 🔐 Auth Routes
Route::prefix('auth')->controller(AuthController::class)->group(function () {
    Route::post('login', 'login');
    Route::post('signup', 'signup');
    Route::post('forgot-password', 'forgot_password');
    Route::post('logout', 'logout')->middleware('auth:sanctum');
});

// 🔒 Protected Routes
Route::middleware('auth:sanctum')->group(function () {

    // 🧑 User Routes
    Route::prefix('user')->controller(UserController::class)->group(function () {
        Route::post('update-password', 'update_password');
        Route::post('update-data', 'update_userdata');
        Route::post('disable', 'account_disable');
        Route::get('wishlist', 'my_wishlist');
        Route::get('wishlist/toggle', 'toggle_wishlist_items');
        Route::get('courses', 'my_courses');
        Route::get('progress/save', 'save_course_progress');
    });

    // 📚 Course Routes
    Route::prefix('courses')->controller(CourseController::class)->group(function () {
        Route::get('top', 'top_courses');
        Route::get('search', 'courses_by_search_string');
        Route::get('filter', 'filter_course');
        Route::get('by-category', 'category_wise_course');
        Route::get('by-category-subcategory', 'category_subcategory_wise_course');
        Route::get('details', 'course_details_by_id');
        Route::get('sections', 'sections');
        Route::get('languages', 'languages');
        Route::get('enroll/free/{course_id}', 'free_course_enroll');
    });

    // 🗂️ Category Routes
    Route::prefix('categories')->controller(CategoryController::class)->group(function () {
        Route::get('/', 'categories');
        Route::get('all', 'all_categories');
        Route::get('details', 'category_details');
        Route::get('sub/{id}', 'sub_categories');
    });

    // 🛒 Cart Routes
    Route::prefix('cart')->controller(CartController::class)->group(function () {
        Route::get('list', 'cart_list');
        Route::get('toggle', 'toggle_cart_items');
        Route::get('tools', 'cart_tools');
    });

    // 💳 Payment Routes
    Route::prefix('payment')->controller(PaymentController::class)->group(function () {
        Route::get('{token}', 'payment');
        Route::get('token', 'token');
    });

    // 📅 Zoom/Live Class Routes
    Route::prefix('zoom')->controller(ZoomController::class)->group(function () {
        Route::get('settings', 'zoom_settings');
        Route::get('meetings', 'live_class_schedules');
    });
});
