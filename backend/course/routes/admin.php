<?php

use App\Http\Controllers\Admin\BlogCategoryController;
use App\Http\Controllers\Admin\BlogController;
use App\Http\Controllers\Admin\BootcampCategoryController;
use App\Http\Controllers\Admin\BootcampController;
use App\Http\Controllers\Admin\BootcampLiveClassController;
use App\Http\Controllers\Admin\BootcampModuleController;
use App\Http\Controllers\Admin\BootcampResourceController;
use App\Http\Controllers\Admin\CategoryController;
use App\Http\Controllers\Admin\ContactController;
use App\Http\Controllers\Admin\CouponController;
use App\Http\Controllers\Admin\CourseController;
use App\Http\Controllers\Admin\CurriculumController;
use App\Http\Controllers\Admin\DashboardController;
use App\Http\Controllers\Admin\InvoiceController;
use App\Http\Controllers\Admin\LanguageController;
use App\Http\Controllers\Admin\LiveClassController;
use App\Http\Controllers\Admin\MessageController;
use App\Http\Controllers\Admin\NewsletterController;
use App\Http\Controllers\Admin\OfflinePaymentController;
use App\Http\Controllers\Admin\QuestionController;
use App\Http\Controllers\Admin\QuizController;
use App\Http\Controllers\Admin\ReportController;
use App\Http\Controllers\Admin\SeoController;
use App\Http\Controllers\Admin\SettingController;
use App\Http\Controllers\Admin\TutorBookingController;
use App\Http\Controllers\Admin\UsersController;
use App\Http\Middleware\Admin;
use App\Http\Middleware\AdminMiddleware;
use Illuminate\Support\Facades\Route;

Route::name('admin.')->prefix('admin')->middleware(AdminMiddleware::class)->group(function () {
    Route::get('dashboard', [DashboardController::class, 'index'])->name('dashboard');

    // === Category ===
    Route::controller(CategoryController::class)->group(function () {
        Route::get('categories', 'index')->name('categories');
        Route::get('category/create', 'create')->name('category.create');
        Route::post('category/store', 'store')->name('category.store');
        Route::get('category/edit', 'edit')->name('category.edit');
        Route::post('category/update/{id}', 'update')->name('category.update');
        Route::get('category/delete/{id}', 'delete')->name('category.delete');
    });

    // === Course ===
    Route::controller(CourseController::class)->group(function () {
        Route::get('courses', 'index')->name('courses');
        Route::get('course/create', 'create')->name('course.create');
        Route::post('course/store', 'store')->name('course.store');
        Route::any('course/edit/{id}', 'edit')->name('course.edit');
        Route::post('course/update/{id}', 'update')->name('course.update');
        Route::get('course/delete/{id}', 'delete')->name('course.delete');
        Route::get('course/draft/{id}', 'draft')->name('course.draft');
        Route::post('course/approval/{id}', 'approval')->name('course.approval');
        Route::get('course/duplicate/{id}', 'duplicate')->name('course.duplicate');
        Route::get('course/status/{type}/{id}', 'status')->name('course.status');
    });

    // === Invoice ===
    Route::get('invoice/{id?}', [InvoiceController::class, 'invoice'])->name('invoice');

    // === Curriculum ===
    Route::controller(CurriculumController::class)->group(function () {
        Route::post('section', 'store')->name('section.store');
        Route::post('section/update', 'update')->name('section.update');
        Route::get('section/delete/{id}', 'delete')->name('section.delete');
        Route::post('section/sort', 'section_sort')->name('section.sort');

        Route::post('lesson', 'lesson_store')->name('lesson.store');
        Route::post('lesson/edit', 'lesson_edit')->name('lesson.edit');
        Route::get('lesson/delete/{id}', 'lesson_delete')->name('lesson.delete');
        Route::post('lesson/sort', 'lesson_sort')->name('lesson.sort');
    });

    // === Users (Admin, Instructor, Student) ===
    Route::controller(UsersController::class)->group(function () {
        Route::get('admins', 'admin_index')->name('admins.index');
        Route::get('admin/create', 'admin_create')->name('admins.create');
        Route::post('admin/store', 'admin_store')->name('admins.store');
        Route::get('admin/edit/{id}', 'admin_edit')->name('admins.edit');
        Route::post('admin/update/{id}', 'admin_update')->name('admins.update');
        Route::get('admin/delete/{id}', 'admin_delete')->name('admins.delete');
        Route::get('admin/permissions/{user_id}', 'admin_permission')->name('admins.permission');
        Route::any('admin/permissions/store/{user_id?}', 'admin_permission_store')->name('admins.permission.store');

        Route::get('manage_profile', 'manage_profile')->name('manage.profile');
        Route::post('manage_profile/update', 'manage_profile_update')->name('manage.profile.update');

        Route::get('instructor', 'instructor_index')->name('instructor.index');
        Route::get('instructor_create/{id?}', 'instructor_create')->name('instructor.create');
        Route::post('instructor/store/{id?}', 'instructor_store')->name('instructor.store');
        Route::get('instructor_edit/{id?}', 'instructor_edit')->name('instructor.edit');
        Route::post('instructor/update/{id}', 'instructor_update')->name('instructor.update');
        Route::get('instructor/delete/{id}', 'instructor_delete')->name('instructor.delete');
        Route::get('instructor/view_course', 'instructor_view_course')->name('instructor.course');
        Route::get('instructor_payout', 'instructor_payout')->name('instructor.payout');
        Route::get('instructor_payout/filter', 'instructor_payout_filter')->name('instructor.payout.filter');
        Route::get('instructor_payout/invoice/{id?}', 'instructor_payout_invoice')->name('instructor.payout.invoice');
        Route::post('instructor_payment', 'instructor_payment')->name('instructor.payment');
        Route::get('instructor_setting', 'instructor_setting')->name('instructor.setting');
        Route::post('instructor/setting/store', 'instructor_setting_store')->name('instructor.setting.store');
        Route::get('instructor_application', 'instructor_application')->name('instructor.application');
        Route::get('instructor_application/approve/{id}', 'instructor_application_approve')->name('instructor.application.approve');
        Route::get('instructor_application/delete/{id}', 'instructor_application_delete')->name('instructor.application.delete');
        Route::get('instructor_application/document/download/{id}', 'instructor_application_download')->name('instructor.application.download');
        Route::get('instructor/{id}/revoke-access', 'revokeAccess')->name('instructor.revoke_access');

        Route::get('student', 'student_index')->name('student.index');
        Route::get('student/create', 'student_create')->name('student.create');
        Route::post('student/store/{id?}', 'student_store')->name('student.store');
        Route::get('student/edit/{id}', 'student_edit')->name('student.edit');
        Route::post('student/update/{id}', 'student_update')->name('student.update');
        Route::get('student/delete/{id}', 'student_delete')->name('student.delete');

        Route::get('enroll_history', 'enroll_history')->name('enroll.history');
        Route::get('enroll_history/delete/{id}', 'enroll_history_delete')->name('enroll.history.delete');
        Route::get('enroll_student', 'student_enrol')->name('student.enroll');
        Route::get('get/students', 'student_get')->name('student.get');
        Route::post('post/students', 'student_post')->name('student.post');
    });

    // === Blog Posts ===
    Route::controller(BlogController::class)->group(function () {
        Route::get('blogs', 'index')->name('blogs');
        Route::get('blog/create', 'create')->name('blog.create');
        Route::post('blog/store', 'store')->name('blog.store');
        Route::get('blog/edit/{id}', 'edit')->name('blog.edit');
        Route::get('blog/delete/{id}', 'delete')->name('blog.delete');
        Route::post('blog/update/{id}', 'update')->name('blog.update');
        Route::get('blog/status/{id}', 'status')->name('blog.status');
        Route::get('blog/pending', 'pending')->name('blog.pending');
        Route::get('blog/settings', 'settings')->name('blog.settings');
        Route::post('blog/settings/update', 'update_settings')->name('blog.settings.update');
    });

    // === Blog Categories ===
    Route::controller(BlogCategoryController::class)->group(function () {
        Route::get('blog/category', 'index')->name('blog.category');
        Route::post('blog/category/create', 'create')->name('blog.category.create');
        Route::post('blog/category/store', 'store')->name('blog.category.store');
        Route::get('blog/category/delete/{id}', 'delete')->name('blog.category.delete');
        Route::post('blog/category/update/{id}', 'update')->name('blog.category.update');
    });

    // === Newsletter ===
    Route::controller(NewsletterController::class)->group(function () {
        Route::get('newsletters', 'index')->name('newsletter');
        Route::post('newsletter-statistics', 'newsletter_statistics')->name('newsletter_statistics');
        Route::post('newsletter/store', 'store')->name('newsletter.store');
        Route::get('newsletters/delete/{id}', 'delete')->name('newsletter.delete');
        Route::post('newsletter/update/{id}', 'update')->name('newsletter.update');
        Route::get('newsletter/subscribers', 'subscribers')->name('subscribed_user');
        Route::get('subscribed_user/delete/{id}', 'subscribed_user_delete')->name('subscribed_user.delete');
        Route::get('newsletters_form', 'newsletters_form')->name('newsletters.form');
        Route::get('get_user', 'get_user')->name('get.user');
        Route::post('send/newsletters', 'send_newsletters')->name('send.newsletters');
    });

    // === Reports ===
    Route::controller(ReportController::class)->group(function () {
        Route::get('admin_revenue', 'admin_revenue')->name('revenue');
        Route::get('admin_revenue/delete/{id}', 'admin_revenue_delete')->name('revenue.delete');
        Route::get('instructor_revenue', 'instructor_revenue')->name('instructor.revenue');
        Route::get('instructor_revenue/delete/{id}', 'instructor_revenue_delete')->name('instructor_revenue.delete');
        Route::get('purchase_history', 'purchase_history')->name('purchase.history');
        Route::get('purchase_history/invoice/{id?}', 'purchase_history_invoice')->name('purchase.history.invoice');
    });

    // === Bootcamp Categories ===
    Route::controller(BootcampCategoryController::class)->group(function () {
        Route::get('bootcamp/categories', 'index')->name('bootcamp.categories');
        Route::post('bootcamp/category/store', 'store')->name('bootcamp.category.store');
        Route::get('bootcamp/category/delete/{id}', 'delete')->name('bootcamp.category.delete');
        Route::post('bootcamp/category/update/{id}', 'update')->name('bootcamp.category.update');
    });

    // === Bootcamps ===
    Route::controller(BootcampController::class)->group(function () {
        Route::get('bootcamps/{type?}', 'index')->name('bootcamps');
        Route::get('bootcamp/create', 'create')->name('bootcamp.create');
        Route::get('bootcamp/edit/{id}', 'edit')->name('bootcamp.edit');
        Route::post('bootcamp/store', 'store')->name('bootcamp.store');
        Route::get('bootcamp/delete/{id}', 'delete')->name('bootcamp.delete');
        Route::post('bootcamp/update/{id}', 'update')->name('bootcamp.update');
        Route::get('bootcamp/status/{id}', 'status')->name('bootcamp.status');
        Route::get('bootcamp/duplicate/{id}', 'duplicate')->name('bootcamp.duplicate');
        Route::get('bootcamp/purchase/history', 'purchase_history')->name('bootcamp.purchase.history');
        Route::get('bootcamp/purchase/invoice/{id}', 'invoice')->name('bootcamp.purchase.invoice');
    });

    // === Bootcamp Modules ===
    Route::controller(BootcampModuleController::class)->group(function () {
        Route::post('bootcamp/module/store', 'store')->name('bootcamp.module.store');
        Route::get('bootcamp/module/delete/{id}', 'delete')->name('bootcamp.module.delete');
        Route::post('bootcamp/module/update/{id}', 'update')->name('bootcamp.module.update');
        Route::get('bootcamp/module/sort', 'sort')->name('bootcamp.module.sort');
    });

    // === Bootcamp Live Classes ===
    Route::controller(BootcampLiveClassController::class)->group(function () {
        Route::post('bootcamp/live-class/store', 'store')->name('bootcamp.live.class.store');
        Route::get('bootcamp/live-class/delete/{id}', 'delete')->name('bootcamp.live.class.delete');
        Route::post('bootcamp/live-class/update/{id}', 'update')->name('bootcamp.live.class.update');
        Route::get('bootcamp/live-class/sort', 'sort')->name('bootcamp.live.class.sort');
        Route::get('bootcamp/live/class/join/{topic}', 'join_class')->name('bootcamp.live.class.join');
        Route::get('bootcamp/live/class/end/{id}', 'stop_class')->name('bootcamp.class.end');
        Route::get('update/on/class/end/', 'update_on_end_class')->name('update.on.end.class');
    });

    // === Bootcamp Resources ===
    Route::controller(BootcampResourceController::class)->group(function () {
        Route::post('bootcamp/resource/store', 'store')->name('bootcamp.resource.store');
        Route::get('bootcamp/resource/delete/{id}', 'delete')->name('bootcamp.resource.delete');
        Route::get('bootcamp/resource/download/{id}', 'download')->name('bootcamp.resource.download');
    });

    // === Tutor Booking ===
    Route::controller(TutorBookingController::class)->group(function () {
        Route::get('tutor-booking/subjects', 'subjects')->name('tutor_subjects');
        Route::get('tutor-booking/subject/create', 'tutor_subject_create')->name('tutor_subject_create');
        Route::post('tutor-booking/subject/store', 'tutor_subject_store')->name('tutor_subject_store');
        Route::get('tutor-booking/subject/edit', 'tutor_subject_edit')->name('tutor_subject_edit');
        Route::post('tutor-booking/subject/update/{id}', 'tutor_subject_update')->name('tutor_subject_update');
        Route::get('tutor-booking/subject/subject-status-update/{id}/{status}', 'tutor_subject_status')->name('tutor_subject_status');
        Route::get('tutor-booking/subject/delete/{id}', 'tutor_subject_delete')->name('tutor_subject_delete');

        Route::get('tutor-booking/tutor-categories', 'tutor_categories')->name('tutor_categories');
        Route::get('tutor-booking/category/create', 'tutor_category_create')->name('tutor_category_create');
        Route::post('tutor-booking/category/store', 'tutor_category_store')->name('tutor_category_store');
        Route::get('tutor-booking/category/edit', 'tutor_category_edit')->name('tutor_category_edit');
        Route::post('tutor-booking/category/update/{id}', 'tutor_category_update')->name('tutor_category_update');
        Route::get('tutor-booking/category/category-status-update/{id}/{status}', 'tutor_category_status')->name('tutor_category_status');
        Route::get('tutor-booking/category/delete/{id}', 'tutor_category_delete')->name('tutor_category_delete');
    });

    // === Settings ===
    Route::controller(SettingController::class)->group(function () {
        Route::get('system_settings', 'system_settings')->name('system.settings');
        Route::post('system_settings/update', 'system_settings_update')->name('system.settings.update');

        Route::get('website_settings', 'website_settings')->name('website.settings');
        Route::post('website_settings/update', 'website_settings_update')->name('website.settings.update');

        Route::get('drip_content_settings', 'drip_content_settings')->name('drip.settings');
        Route::post('drip_content_settings/update', 'drip_content_settings_update')->name('drip.settings.update');

        Route::get('payment_settings', 'payment_settings')->name('payment.settings');
        Route::post('payment_settings/update', 'payment_settings_update')->name('payment.settings.update');

        Route::get('manage_language', 'manage_language')->name('manage.language');
        Route::post('language/store', 'language_store')->name('language.store');
        Route::post('language/direction/update', 'language_direction_update')->name('language.direction.update');
        Route::post('language/import', 'language_import')->name('language.import');
        Route::get('language/delete/{id}', 'language_delete')->name('language.delete');
        Route::get('language/export/{id}', 'language_export')->name('language.export');
        Route::get('language/phrase/edit/{lan_id}', 'edit_phrase')->name('language.phrase.edit');
        Route::post('language/phrase/update/{phrase_id?}', 'update_phrase')->name('language.phrase.update');
        Route::get('language/phrase/import/{lan_id}', 'phrase_import')->name('language.phrase.import');

        Route::get('notification_settings', 'notification_settings')->name('notification.settings');
        Route::any('notification_settings/store/{param1}/{id?}', 'notification_settings_store')->name('notification.settings.store');

        Route::get('player-settings', 'player_settings')->name('player.settings');
        Route::post('player-settings/update', 'player_settings_update')->name('player.settings.update');

        Route::get('certificate_settings', 'certificate')->name('certificate.settings');
        Route::post('certificate/update/template', 'certificate_update_template')->name('certificate.update.template');
        Route::get('certificate/builder', 'certificate_builder')->name('certificate.builder');
        Route::post('certificate/builder/update', 'certificate_builder_update')->name('certificate.certificate.builder.update');

        Route::get('user/review', 'user_review_add')->name('review.create');
        Route::post('user/review/stor', 'user_review_stor')->name('review.store');
        Route::get('user/review/edit/{id}', 'review_edit')->name('review.edit');
        Route::post('user/review/update/{id}', 'review_update')->name('review.update');
        Route::get('user/review/delete/{id}', 'review_delete')->name('review.delete');

        Route::post('update/home/{id}', 'update_home')->name('update.home');

        // === API Configs ===
        Route::get('api/configurations', 'api_configurations')->name('api.configurations');
        Route::post('api/configuration/update/{type}', 'api_configuration_update')->name('api.configuration.update');
    });

    // === SEO ===
    Route::controller(SeoController::class)->group(function () {
        Route::get('seo_settings/{route?}', 'seo_settings')->name('seo.settings');
        Route::post('seo_settings/update/{route}', 'seo_settings_update')->name('seo.settings.update');
    });

    // === Offline Payments ===
    Route::controller(OfflinePaymentController::class)->group(function () {
        Route::get('offline-payments', 'index')->name('offline.payments');
        Route::get('offline-payment/doc/{id}', 'download_doc')->name('offline.payment.doc');
        Route::get('offline-payment/accept/{id}', 'accept_payment')->name('offline.payment.accept');
        Route::get('offline-payment/decline/{id}', 'decline_payment')->name('offline.payment.decline');
        Route::get('offline-payment/delete/{id}', 'delete_payment')->name('offline.payment.delete');
    });

    // === Coupons ===
    Route::controller(CouponController::class)->group(function () {
        Route::get('coupons', 'index')->name('coupons');
        Route::get('coupon/create', 'create')->name('coupon.create');
        Route::post('coupon/store', 'store')->name('coupon.store');
        Route::get('coupon/delete/{id}', 'delete')->name('coupon.delete');
        Route::get('coupon/edit/{id}', 'edit')->name('coupon.edit');
        Route::post('coupon/update/{id}', 'update')->name('coupon.update');
        Route::get('coupon/status/{id}', 'status')->name('coupon.status');
    });

    // === Quizzes ===
    Route::controller(QuizController::class)->group(function () {
        Route::post('course/quiz/store', 'store')->name('course.quiz.store');
        Route::get('course/quiz/delete/{id}', 'delete')->name('course.quiz.delete');
        Route::post('course/quiz/update/{id}', 'update')->name('course.quiz.update');
        Route::get('quiz/participant/result', 'result')->name('quiz.participant.result');
        Route::get('quiz/result/preview', 'result_preview')->name('quiz.result.preview');
    });

    // === Questions ===
    Route::controller(QuestionController::class)->group(function () {
        Route::post('course/question/store', 'store')->name('course.question.store');
        Route::get('course/question/delete/{id}', 'delete')->name('course.question.delete');
        Route::post('course/question/update/{id}', 'update')->name('course.question.update');
        Route::get('course/question/sort/', 'sort')->name('course.question.sort');
        Route::get('load/question/type/', 'load_type')->name('load.question.type');
    });

    // === Live Classes ===
    Route::controller(LiveClassController::class)->group(function () {
        Route::post('live-class/store/{course_id}', 'live_class_store')->name('live.class.store');
        Route::post('live-class/update/{id}', 'live_class_update')->name('live.class.update');
        Route::get('live-class/delete/{id}', 'live_class_delete')->name('live.class.delete');
        Route::get('live-class/start/{id}', 'live_class_start')->name('live.class.start');
        Route::get('live-class/settings', 'live_class_settings')->name('live.class.settings');
        Route::post('live-class/settings/update', 'update_live_class_settings')->name('live.class.settings.update');
    });

    // === Contact Messages ===
    Route::controller(ContactController::class)->group(function () {
        Route::any('contacts', 'index')->name('contacts');
        Route::post('reply', 'reply')->name('reply');
        Route::get('contact/delete/{id}', 'contact_delete')->name('contact.delete');
    });

    // === Messages ===
    Route::controller(MessageController::class)->group(function () {
        Route::post('message/store', 'store')->name('message.store');
        Route::post('message/thread/store', 'thread_store')->name('message.thread.store');
        Route::get('message/{message_thread?}', 'message')->name('message');
    });

    // === Language Select Route ===
    Route::get('select-language/{language}', [LanguageController::class, 'select_lng'])->name('select.language');
});
