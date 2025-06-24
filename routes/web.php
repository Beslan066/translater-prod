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

    Route::post('/upload', [SentenceController::class, 'upload'])->name('sentences.upload');
    Route::get('/logs/view', [SentenceController::class, 'getLogs'])->name('logs.view');

    Route::post('/sentences/{sentence}/approve', [SentenceController::class, 'approveTranslation'])->name('sentences.approve');
    Route::post('/sentences/{sentence}/delay', [SentenceController::class, 'delayTranslation'])->name('sentences.delay');    Route::post('/sentences/{sentence}/reject', [SentenceController::class, 'rejectTranslation'])->name('sentences.reject');
    Route::get('/sentences/moderating', [SentenceController::class, 'moderate'])->name('sentence.moderate');
    Route::get('/sentences/completed', [HomeController::class, 'completedSentences'])->name('sentence.completed');
    Route::get('/sentences/district', [HomeController::class, 'districtSentences'])->name('sentence.district');
    Route::patch('/sentences/{sentence}', [SentenceController::class, 'resetTeacherSentence'])->name('sentence.update');
    Route::delete('/sentences', [HomeController::class, 'deleteSentences'])->name('sentences.delete');
    Route::get('/search', [HomeController::class, 'search'])->name('sentences.search');

    Route::post('/export/sentences', [SentenceController::class, 'exportSentences']);
    Route::get('/export/progress/{batchId}', [SentenceController::class, 'checkExportProgress']);
    Route::get('/export/download/{filename}', [SentenceController::class, 'downloadExport']);



    Route::get('/users', [UserController::class, 'index'])->name('users.index');
    Route::get('/users/{user}/edit', [UserController::class, 'edit'])->name('users.edit');
    Route::patch('/users/{user}', [UserController::class, 'update'])->name('users.update');
    Route::patch('/users/{user}/role', [UserController::class, 'updateRole'])->name('users.updateRole');
    Route::delete('/users/{user}', [UserController::class, 'deleteUser'])->name('user.delete');
    Route::get('/users/export', [UserController::class, 'export'])->name('users.export');
    Route::get('/users/search', [UserController::class, 'search'])->name('users.search');





    Route::get('/translate', [SentenceController::class, 'getSentence'])->name('translate');

    Route::post('/translations/{translation}/edit', [SentenceController::class, 'editTranslation'])->name('translations.edit');
    Route::post('/translate', [SentenceController::class, 'saveTranslation'])->name('translate.save');


});


Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

require __DIR__.'/auth.php';
