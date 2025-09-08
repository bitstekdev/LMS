<?php

use App\Http\Controllers\Course\FileController;
use App\Http\Controllers\Course\ForumController;
use App\Http\Controllers\Course\PlayerController;
use Illuminate\Support\Facades\Route;

Route::middleware('auth')->group(function () {

    /**
     * 🎥 Course Player Routes
     */
    Route::prefix('player')->controller(PlayerController::class)->group(function () {
        Route::get('/play-course/{slug}/{id?}', 'course_player')->name('course.player');
        Route::post('/set-watch-history', 'set_watch_history')->name('set_watch_history');
        Route::get('/prepend/watermark', 'prepend_watermark')->name('prepend_watermark');
    });

    /**
     * 💬 Forum Routes
     */
    Route::prefix('forum')->as('forum.')->controller(ForumController::class)->group(function () {
        Route::get('/questions', 'index')->name('questions');
        Route::get('/question/create', 'create')->name('question.create');
        Route::post('/question/store', 'store')->name('question.store');
        Route::get('/question/delete/{id}', 'delete')->name('question.delete');
        Route::get('/question/edit', 'edit')->name('question.edit');
        Route::post('/question/update/{id}', 'update')->name('question.update');

        Route::get('/question/likes/{id}', 'likes')->name('question.likes');
        Route::get('/question/dislikes/{id}', 'dislikes')->name('question.dislikes');
        Route::get('/tab/active', 'tab_active')->name('tab.active');

        Route::get('/question/reply/create', 'create_reply')->name('question.reply.create');
        Route::post('/question/reply/store', 'store_reply')->name('question.reply.store');
        Route::get('/question/reply/edit', 'edit_reply')->name('question.reply.edit');
        Route::post('/question/reply/update/{id}', 'update_reply')->name('question.reply.update');
    });

    /**
     * 📁 Course File Routes
     */
    Route::prefix('files')->as('files.')->controller(FileController::class)->group(function () {
        Route::get('/', 'get_file')->name('get');
        Route::get('/videos', 'get_video_file')->name('videos');
        Route::get('/pdf-canvas/{course_id?}/{lesson_id?}', 'pdf_canvas')->name('pdf_canvas');
    });
});
