<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MoodController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProductivityReportController;
use App\Http\Controllers\DashboardController;

Route::middleware(['auth', 'verified'])->group(function () {

    Route::resource('tasks', TaskController::class);

    Route::patch('/tasks/{task}/complete', 
        [TaskController::class, 'complete']
    )->name('tasks.complete');

});

Route::get('/', function () {
    return view('welcome');
});

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified', 'student.mood.checked'])
    ->name('dashboard');

Route::middleware(['auth', 'verified'])->group(function () {
    Route::view('/calendar', 'student.calendar')->name('calendar');
    Route::view('/my-penguin', 'student.penguin')->name('penguin');
});

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::resource('moods', MoodController::class);
    Route::resource('notifications', NotificationController::class);
});

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

    Route::get('/admin/productivity', [ProductivityReportController::class, 'index'])
        ->name('admin.productivity');

});

require __DIR__.'/auth.php';
