<?php

use App\Http\Controllers\Home\UserController;
use App\Http\Controllers\HomeController;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\SentenceController;
use Illuminate\Support\Facades\Route;


Route::get('/dashboard', function () {
    return view('dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');



Route::middleware('auth')->group(function () {


    Route::get('/', [HomeController::class, 'index'])->name('home');

    Route::get('/upload', [SentenceController::class, 'upload']);
    Route::get('/upload-progress', [SentenceController::class, 'progress'])->name('sentences.progress');


    Route::post('/upload', [SentenceController::class, 'upload'])->name('sentences.upload');
    Route::post('/sentences/{sentence}/approve', [SentenceController::class, 'approveTranslation'])->name('sentences.approve');
    Route::post('/sentences/{sentence}/reject', [SentenceController::class, 'rejectTranslation'])->name('sentences.reject');
    Route::get('/sentences/moderating', [SentenceController::class, 'moderate'])->name('sentence.moderate');
    Route::get('/sentences/completed', [HomeController::class, 'completedSentences'])->name('sentence.completed');
    Route::get('/sentences/district', [HomeController::class, 'districtSentences'])->name('sentence.district');
    Route::patch('/sentences/{sentence}', [SentenceController::class, 'resetTeacherSentence'])->name('sentence.update');
    Route::delete('/sentences', [HomeController::class, 'deleteSentences'])->name('sentences.delete');
    Route::get('/search', [HomeController::class, 'search'])->name('sentences.search');



    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
    Route::delete('/users/{user}', [UserController::class, 'deleteUser'])->name('user.delete');





    Route::get('/translate', [SentenceController::class, 'getSentence'])->name('translate');
    Route::post('/translate', [SentenceController::class, 'saveTranslation'])->name('translate.save');


});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
