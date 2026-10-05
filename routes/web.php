<?php

use App\Http\Controllers\NotificationController;
use App\Http\Controllers\ProfileController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\TaskController;
use App\Http\Controllers\MoodController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\ProductivityReportController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\CalendarController;
use App\Http\Controllers\PenguinController;
use App\Http\Controllers\AdminStudentController;
use App\Http\Controllers\AdminEmotionController;
use App\Http\Controllers\AdminNotificationController;
use App\Http\Controllers\AdminProfileController;

Route::middleware(['auth', 'verified', 'student.mood.checked'])->group(function () {

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

Route::middleware(['auth', 'verified', 'student.mood.checked'])->group(function () {
    Route::get('/calendar', [CalendarController::class, 'index'])->name('calendar');
    Route::get('/my-penguin', [PenguinController::class, 'index'])
    ->name('penguin');
});

Route::middleware(['auth', 'verified', 'student.mood.checked'])->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::middleware(['auth', 'verified'])->group(function () {
    Route::get('/moods/create', [MoodController::class, 'create'])->name('moods.create');
    Route::post('/moods', [MoodController::class, 'store'])->name('moods.store');
});

Route::middleware(['auth', 'verified', 'student.mood.checked'])->group(function () {
    Route::resource('moods', MoodController::class)->except(['create', 'store']);
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
});

Route::middleware(['auth', 'verified', 'role:admin'])->group(function () {

    Route::get('/admin/dashboard', [AdminController::class, 'dashboard'])
        ->name('admin.dashboard');

    Route::get('/admin/productivity', [ProductivityReportController::class, 'index'])
        ->name('admin.productivity');

    Route::get('/admin/students', [AdminStudentController::class, 'index'])
    ->name('admin.students.index');

Route::get('/admin/students/create', [AdminStudentController::class, 'create'])
    ->name('admin.students.create');

Route::post('/admin/students', [AdminStudentController::class, 'store'])
    ->name('admin.students.store');

Route::get('/admin/students/{user}', [AdminStudentController::class, 'show'])
    ->name('admin.students.show');

    Route::get('/admin/emotions', [AdminEmotionController::class, 'index'])->name('admin.emotions.index');
    Route::get('/admin/emotions/{user}', [AdminEmotionController::class, 'show'])->name('admin.emotions.show');

    Route::get('/admin/notifications', [AdminNotificationController::class, 'index'])->name('admin.notifications.index');
    Route::get('/admin/notifications/create', [AdminNotificationController::class, 'create'])->name('admin.notifications.create');
    Route::post('/admin/notifications', [AdminNotificationController::class, 'store'])->name('admin.notifications.store');

    Route::get('/admin/profile', [AdminProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::patch('/admin/profile', [AdminProfileController::class, 'update'])->name('admin.profile.update');

});

require __DIR__.'/auth.php';
