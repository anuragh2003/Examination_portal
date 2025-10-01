<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\ExamController;

Route::get('/', function () {
    return view('login');
});
Route::get('/login', function () {
    return view('login');
})->name('login');

// Login/Logout
Route::post('/login', [UserController::class, 'login'])->name('login.post');
Route::post('/logout', [UserController::class, 'logout'])->name('logout');

// Dashboard and Exams routes (web authentication via session)
Route::middleware('web')->group(function () {
    Route::get('/dashboard', [ExamController::class, 'index'])->name('dashboard');
    Route::get('/exams/create', [ExamController::class, 'create'])->name('exams.create');
    Route::post('/exams', [ExamController::class, 'store'])->name('exams.store');
    Route::get('/exams/{uuid}', [ExamController::class, 'show'])->name('exams.show');
    Route::delete('/exams/{uuid}', [ExamController::class, 'destroy'])->name('exams.destroy'); 
    Route::get('/exams/{uuid}/edit', [ExamController::class, 'edit'])->name('exams.edit');
Route::put('/exams/{uuid}', [ExamController::class, 'update'])->name('exams.update');

});
