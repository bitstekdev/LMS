<?php

use App\Http\Controllers\Instructor\BlogController;
use App\Http\Controllers\Instructor\BootcampController;
use App\Http\Controllers\Instructor\BootcampLiveClassController;
use App\Http\Controllers\Instructor\BootcampModuleController;
use App\Http\Controllers\Instructor\BootcampResourceController;
use App\Http\Controllers\Instructor\CourseController;
use App\Http\Controllers\Instructor\DashboardController;
use App\Http\Controllers\Instructor\LanguageController;
use App\Http\Controllers\Instructor\LessonController;
use App\Http\Controllers\Instructor\LiveClassController;
use App\Http\Controllers\Instructor\MyProfileController;
use App\Http\Controllers\Instructor\PayoutController;
use App\Http\Controllers\Instructor\PayoutSettingsController;
use App\Http\Controllers\Instructor\QuestionController;
use App\Http\Controllers\Instructor\QuizController;
use App\Http\Controllers\Instructor\SalesReportController;
use App\Http\Controllers\Instructor\SectionController;
use App\Http\Controllers\Instructor\TeamTrainingController;
use App\Http\Controllers\Instructor\TutorBookingController;
use App\Http\Middleware\InstructorBlogPermissionMiddleware;
use App\Http\Middleware\InstructorMiddleware;
use Illuminate\Support\Facades\Route;

Route::prefix('instructor')->name('instructor.')->middleware(InstructorMiddleware::class)->group(function () {

    /** ------------------- Dashboard ------------------- **/
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    /** ------------------- Courses ------------------- **/
    Route::prefix('course')->controller(CourseController::class)->group(function () {
        Route::get('courses', 'index')->name('courses');
        Route::get('create', 'create')->name('create');
        Route::post('store', 'store')->name('store');
        Route::get('edit/{id}', 'edit')->name('edit');
        Route::post('update/{id}', 'update')->name('update');
        Route::get('duplicate/{id}', 'duplicate')->name('duplicate');
        Route::get('delete/{id}', 'delete')->name('delete');
        Route::get('draft/{id}', 'draft')->name('draft');
        Route::get('status/{type}/{id}', 'status')->name('status');
    });

    /** ------------------- Sections ------------------- **/
    Route::prefix('section')->controller(SectionController::class)->group(function () {
        Route::post('store', 'store')->name('section.store');
        Route::post('update', 'update')->name('section.update');
        Route::get('delete/{id}', 'delete')->name('section.delete');
        Route::post('sort', 'sort')->name('section.sort');
    });

    /** ------------------- Lessons ------------------- **/
    Route::prefix('lesson')->controller(LessonController::class)->group(function () {
        Route::post('store', 'store')->name('lesson.store');
        Route::post('update', 'update')->name('lesson.update');
        Route::get('delete/{id}', 'delete')->name('lesson.delete');
        Route::post('sort', 'sort')->name('lesson.sort');
    });

    /** ------------------- Quizzes ------------------- **/
    Route::prefix('quiz')->controller(QuizController::class)->group(function () {
        Route::post('store', 'store')->name('course.quiz.store');
        Route::get('delete/{id}', 'delete')->name('course.quiz.delete');
        Route::post('update/{id}', 'update')->name('course.quiz.update');
        Route::get('participant/result', 'result')->name('quiz.participant.result');
        Route::get('result/preview', 'result_preview')->name('quiz.result.preview');
    });

    /** ------------------- Questions ------------------- **/
    Route::prefix('question')->controller(QuestionController::class)->group(function () {
        Route::post('store', 'store')->name('course.question.store');
        Route::get('delete/{id}', 'delete')->name('course.question.delete');
        Route::post('update/{id}', 'update')->name('course.question.update');
        Route::get('sort', 'sort')->name('course.question.sort');
        Route::get('load-type', 'load_type')->name('load.question.type');
    });

    /** ------------------- Blogs ------------------- **/
    Route::middleware(InstructorBlogPermissionMiddleware::class)->controller(BlogController::class)->group(function () {
        Route::get('blogs', 'index')->name('blogs');
        Route::get('blog/create', 'create')->name('blog.create');
        Route::post('blog/store', 'store')->name('blog.store');
        Route::get('blog/edit/{id}', 'edit')->name('blog.edit');
        Route::post('blog/update/{id}', 'update')->name('blog.update');
        Route::get('blog/delete/{id}', 'delete')->name('blog.delete');
        Route::get('blog/pending', 'pending')->name('blog.pending');
    });

    /** ------------------- Sales Report ------------------- **/
    Route::get('sales-report', [SalesReportController::class, 'index'])->name('sales.report');

    /** ------------------- Payouts ------------------- **/
    Route::prefix('payout')->controller(PayoutController::class)->group(function () {
        Route::get('reports', 'index')->name('payout.reports');
        Route::post('request', 'store')->name('payout.request');
        Route::get('request/delete/{id}', 'delete')->name('payout.delete');
    });

    Route::prefix('payout_setting')->controller(PayoutSettingsController::class)->group(function () {
        Route::get('/', 'payout_setting')->name('payout.setting');
        Route::post('store', 'payout_setting_store')->name('payout.setting.store');
    });

    /** ------------------- My Profile ------------------- **/
    Route::prefix('manage_profile')->controller(MyProfileController::class)->group(function () {
        Route::get('/', 'manage_profile')->name('manage.profile');
        Route::post('update', 'manage_profile_update')->name('manage.profile.update');
    });

    Route::prefix('manage_resume')->controller(MyProfileController::class)->group(function () {
        Route::get('/', 'manage_resume')->name('manage.resume');
        Route::post('education-add', 'education_add')->name('manage.education_add');
        Route::post('education-update/{index}', 'education_update')->name('manage.education_update');
        Route::get('education-remove/{index}', 'education_remove')->name('manage.education.remove');
    });

    /** ------------------- Bootcamps ------------------- **/
    Route::prefix('bootcamp')->controller(BootcampController::class)->group(function () {
        Route::get('/', 'index')->name('bootcamps');
        Route::get('create', 'create')->name('bootcamp.create');
        Route::get('edit/{id}', 'edit')->name('bootcamp.edit');
        Route::post('store', 'store')->name('bootcamp.store');
        Route::post('update/{id}', 'update')->name('bootcamp.update');
        Route::get('delete/{id}', 'delete')->name('bootcamp.delete');
        Route::get('status/{id}', 'status')->name('bootcamp.status');
        Route::get('duplicate/{id}', 'duplicate')->name('bootcamp.duplicate');
        Route::get('purchase/history', 'purchase_history')->name('bootcamp.purchase.history');
        Route::get('purchase/invoice/{id}', 'invoice')->name('bootcamp.purchase.invoice');
    });

    Route::prefix('bootcamp/module')->controller(BootcampModuleController::class)->group(function () {
        Route::post('store', 'store')->name('bootcamp.module.store');
        Route::post('update/{id}', 'update')->name('bootcamp.module.update');
        Route::get('delete/{id}', 'delete')->name('bootcamp.module.delete');
        Route::get('sort', 'sort')->name('bootcamp.module.sort');
    });

    Route::prefix('bootcamp/live-class')->controller(BootcampLiveClassController::class)->group(function () {
        Route::post('store', 'store')->name('bootcamp.live.class.store');
        Route::post('update/{id}', 'update')->name('bootcamp.live.class.update');
        Route::get('delete/{id}', 'delete')->name('bootcamp.live.class.delete');
        Route::get('sort', 'sort')->name('bootcamp.live.class.sort');
        Route::get('join/{topic}', 'join_class')->name('bootcamp.live.class.join');
        Route::get('end/{id}', 'stop_class')->name('bootcamp.class.end');
        Route::get('update/on-end', 'update_on_end_class')->name('update.on.end.class');
    });

    Route::prefix('bootcamp/resource')->controller(BootcampResourceController::class)->group(function () {
        Route::post('store', 'store')->name('bootcamp.resource.store');
        Route::get('delete/{id}', 'delete')->name('bootcamp.resource.delete');
        Route::get('download/{id}', 'download')->name('bootcamp.resource.download');
    });

    /** ------------------- Team Training ------------------- **/
    Route::prefix('team-packages')->controller(TeamTrainingController::class)->group(function () {
        Route::get('/', 'index')->name('team.packages');
        Route::view('create', 'instructor.team_training.create')->name('team.packages.create');
        Route::post('store', 'store')->name('team.packages.store');
        Route::get('purchase/history', 'purchase_history')->name('team.packages.purchase.history');
        Route::get('edit/{id}', 'edit')->middleware('record.exists:team_training_packages,id,user_id')->name('team.packages.edit');
        Route::post('update/{id}', 'update')->name('team.packages.update');
        Route::get('delete/{id}', 'delete')->name('team.packages.delete');
        Route::get('duplicate/{id}', 'duplicate')->name('team.packages.duplicate');
        Route::get('toggle-status/{id}', 'toggle_status')->name('team.toggle.status');
        Route::get('purchase/invoice/{id}', 'invoice')->name('team.packages.purchase.invoice');
    });

    Route::get('get-courses-by-privacy', [TeamTrainingController::class, 'get_courses'])->name('get.courses.by.privacy');
    Route::get('get-courses-price', [TeamTrainingController::class, 'get_course_price'])->name('get.course.price');

    /** ------------------- Tutor Booking ------------------- **/
    Route::controller(TutorBookingController::class)->group(function () {
        Route::prefix('tutor-booking')->group(function () {
            Route::get('my-subjects', 'my_subjects')->name('my_subjects');
            Route::get('my-subject/create', 'my_subject_add')->name('my_subject_add');
            Route::post('my-subject/store', 'my_subject_store')->name('my_subject_store');
            Route::get('my-subject/edit', 'my_subject_edit')->name('my_subject_edit');
            Route::post('my-subject/update/{id}', 'my_subject_update')->name('my_subject_update');
            Route::get('my-subject/delete/{id}', 'my_subject_delete')->name('my_subject_delete');
            Route::get('my-subject/delete-category/{id}', 'my_subject_category_delete')->name('my_subject_category_delete');

            Route::get('manage-schedules', 'manage_schedules')->name('manage_schedules');
            Route::get('manage-schedules-by-date/{date}', 'manage_schedules_by_date')->name('manage_schedules_by_date');
            Route::get('schedule/edit/{id}', 'schedule_edit')->name('schedule_edit');
            Route::post('schedule/update/{id}', 'schedule_update')->name('schedule_update');
            Route::get('schedule/delete/{id}', 'schedule_delete')->name('schedule_delete');
            Route::get('schedule-add', 'add_schedule')->name('add_schedule');
            Route::post('schedule/store', 'schedule_store')->name('schedule_store');
        });

        Route::get('tutor_booking/tutor-booking-list', 'tutor_booking_list')->name('tutor_booking_list');
        Route::get('tutor_booking/tution-class/join/{booking_id}', 'join_class')->name('tution_class.join');
        Route::get('get-subject-by-category-id', 'subject_by_category_id')->name('get.subject_by_category_id');
    });

    /** ------------------- Live Class ------------------- **/
    Route::controller(LiveClassController::class)->group(function () {
        Route::prefix('live-class')->group(function () {
            Route::post('store/{course_id}', 'live_class_store')->name('live.class.store');
            Route::post('update/{id}', 'live_class_update')->name('live.class.update');
            Route::get('delete/{id}', 'live_class_delete')->name('live.class.delete');
            Route::get('start/{id}', 'live_class_start')->name('live.class.start');
            Route::get('settings', 'live_class_settings')->name('live.class.settings');
            Route::post('settings/update', 'update_live_class_settings')->name('live.class.settings.update');
        });
    });
});

/** ------------------- Language Selector ------------------- **/
Route::get('instructor/select-language/{language}', [LanguageController::class, 'select_lng'])->name('instructor.select.language');
