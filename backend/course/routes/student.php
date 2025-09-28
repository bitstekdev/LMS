<?php

use App\Http\Controllers\Student\MyBootcampsController;
use App\Http\Controllers\Student\MyProfileController;
use App\Http\Controllers\Student\QuizController;
use Illuminate\Support\Facades\Route;

// 🧑‍🎓 Student Routes (Protected)
Route::middleware(['auth'])->group(function () {

    // 👤 Profile
    Route::controller(MyProfileController::class)->group(function () {
        Route::get('my-profile', 'index')->name('my.profile');
        Route::post('my-profile/update/{user_id}', 'update')->name('update.profile');
        Route::post('update-profile-picture', 'update_profile_picture')->name('update.profile.picture');
        Route::post('change-password', 'changePassword')->name('password.change');
    });

    // 📝 Quizzes
    Route::controller(QuizController::class)->group(function () {
        Route::post('quiz/submit/{id}', 'quiz_submit')->name('quiz.submit');
        Route::get('load/quiz/result', 'load_result')->name('load.quiz.result');
        Route::get('load/quiz/questions', 'load_questions')->name('load.quiz.questions');
    });

    // 🏕️ Bootcamps
    Route::controller(MyBootcampsController::class)->group(function () {
        Route::get('bootcamp/live/class/join/{topic}', 'join_class')->name('bootcamp.live.class.join');
        Route::get('bootcamp/resource/download/{id}', 'download')->name('bootcamp.resource.download');
        Route::get('bootcamp/resource/play/{file}', 'play')->name('bootcamp.resource.play');
    });
});
