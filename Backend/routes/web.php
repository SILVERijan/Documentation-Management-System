<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Admin\AdminDashboardController;

Route::get('/', function () {
    return redirect()->route('login');
});

Route::get('/documentations', function () {
    return Inertia::render('Dashboard');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/docs/{slug}', function ($slug) {
    return Inertia::render('Dashboard');
})->middleware(['auth'])->name('app.show');

Route::middleware('auth')->group(function () {
    Route::get('/admin', [AdminDashboardController::class, 'index'])->middleware('admin')->name('admin.dashboard');
    Route::resource('admin/users', \App\Http\Controllers\Admin\UserController::class)->middleware('admin')->names('admin.users');
    Route::resource('admin/applications', \App\Http\Controllers\Admin\ApplicationController::class)->middleware('admin')->names('admin.applications');
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/original', function () {
    return Inertia::render('FrontendOriginal');
});

require __DIR__.'/auth.php';
