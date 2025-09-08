<?php

use App\Http\Controllers\Chat\ChatController;
use Illuminate\Support\Facades\Route;

Route::prefix('chat')->middleware(['auth'])->controller(ChatController::class)->group(function () {

    // 📩 Inbox & Messaging
    Route::get('/inbox/{receiver}/{product?}', 'chat')->name('chat');
    Route::get('/inbox/load/data/ajax', 'chat_load')->name('chat.load');
    Route::get('/inbox/read/message/ajax', 'chat_read_option')->name('chat.read');

    // 🧑‍💬 Message routes
    Route::get('/new', 'new_message')->name('new.message');
    Route::get('/profile/search', 'search_chat')->name('search.chat');
    Route::get('/own/remove/{id}', 'remove_chat')->name('remove.chat');
    Route::post('/save', 'chat_save')->name('chat.save');
    Route::post('/react', 'react_chat')->name('react.chat');

    // 📨 General message page
    Route::get('/', 'message')->name('message');
});
