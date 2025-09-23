<?php

use App\Http\Controllers\Frontend\BootcampController;
use App\Http\Controllers\Frontend\CourseController;
use App\Http\Controllers\Frontend\HomeController;
use App\Http\Controllers\Frontend\InstructorController;
use App\Http\Controllers\Frontend\LanguageController;
use App\Http\Controllers\Frontend\PublicFormController;
use App\Http\Controllers\Frontend\StaticPageController;
use App\Http\Controllers\Frontend\TeamTrainingController;
use Illuminate\Support\Facades\Route;

// 🏠 Home Routes
Route::controller(HomeController::class)->group(function () {
    Route::get('/', 'index')->name('home');
    Route::post('/update_watch_history', 'update_watch_history_with_duration')->name('update_watch_history');
    Route::get('home/switch/{id}', 'homepage_switcher')->name('home.switch');
});

// 🎓 Course Routes
Route::controller(CourseController::class)->group(function () {
    Route::get('courses/{category?}', 'index')->name('courses');
    Route::get('change/layout', 'change_layout')->name('change.layout');
    Route::get('course/{slug}', 'course_details')->name('course.details');
});

// 📩 Public Form Routes (Contact + Newsletter)
Route::controller(PublicFormController::class)->group(function () {
    Route::get('contact-us/', 'contactForm')->name('contact.us');
});

// 🧑‍🏫 Instructor Routes
Route::controller(InstructorController::class)->group(function () {
    Route::get('instructors', 'index')->name('instructors');
    Route::get('instructor-details/{name}/{id}', 'show')->name('instructor.details');
});

// 🚀 Bootcamp Routes
Route::controller(BootcampController::class)->group(function () {
    Route::get('bootcamp', 'index')->name('bootcamps');
    Route::get('bootcamp/{slug}', 'show')->name('bootcamp.details');
});

// 🧑‍💼 Team Training Routes
Route::controller(TeamTrainingController::class)->group(function () {
    Route::get('team-packages/{course_category?}', 'index')->name('team.packages');
    Route::get('team-package/{slug}', 'show')->name('team.package.details');
});

// 🌐 Language Selector
Route::get('select/language/', [LanguageController::class, 'selectLanguage'])->name('select.lng');

// 📄 Static Page Routes
Route::controller(StaticPageController::class)->group(function () {
    Route::get('about-us', 'about')->name('about.us');
    Route::get('privacy-policy', 'privacy')->name('privacy.policy');
    Route::get('refund-policy', 'refund')->name('refund.policy');
    Route::get('faq', 'faq')->name('faq');
    Route::get('terms-and-condition', 'terms')->name('terms.condition');
    Route::get('cookie-policy', 'cookie')->name('cookie.policy');
});
