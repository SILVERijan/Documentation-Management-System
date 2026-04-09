<?php

use App\Http\Controllers\ProfileController;
use Illuminate\Foundation\Application;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;
use Inertia\Inertia;

use App\Http\Controllers\Admin\AdminDashboardController;

Route::get('/', function () {
    if (Auth::check()) {
        return redirect()->route('dashboard');
    }
    return Inertia::render('Welcome', [
        'canLogin' => Route::has('login'),
        'canRegister' => Route::has('register'),
    ]);
})->name('welcome');

Route::get('/dashboard', function () {
    $user = Auth::user();
    if ($user->isAdmin()) {
        return redirect()->route('admin.dashboard');
    }
    return redirect()->route('user.documents.index');
})->middleware(['auth', 'verified'])->name('dashboard');

Route::get('/docs/{appSlug}/{docSlug?}', [\App\Http\Controllers\DocumentationController::class, 'show'])
    ->name('app.show.doc');

# ... (existing search route)
Route::get('/api/search/docs', [\App\Http\Controllers\DocumentationController::class, 'search'])->name('docs.search');
Route::get('/attachments/{attachment}/download', [\App\Http\Controllers\User\DocumentAttachmentController::class, 'download'])->name('user.attachments.download');

Route::middleware('auth')->group(function () {
    Route::resource('documents', \App\Http\Controllers\User\DocumentController::class)->names('user.documents');
    Route::get('documents/{document}/sections', [\App\Http\Controllers\User\DocumentSectionController::class, 'index'])->name('user.sections.index');
    Route::post('documents/{document}/sections', [\App\Http\Controllers\User\DocumentSectionController::class, 'store'])->name('user.sections.store');
    Route::put('documents/{document}/sections/{section}', [\App\Http\Controllers\User\DocumentSectionController::class, 'update'])->name('user.sections.update');
    Route::delete('documents/{document}/sections/{section}', [\App\Http\Controllers\User\DocumentSectionController::class, 'destroy'])->name('user.sections.destroy');
    
    // Markdown import
    Route::post('documents/import-markdown', [\App\Http\Controllers\User\MarkdownImportController::class, 'parse'])->name('user.documents.import-markdown');

    // Attachments
    Route::post('documents/{document}/attachments', [\App\Http\Controllers\User\DocumentAttachmentController::class, 'store'])->name('user.attachments.store');
    Route::delete('attachments/{attachment}', [\App\Http\Controllers\User\DocumentAttachmentController::class, 'destroy'])->name('user.attachments.destroy');

    // Media (Editor)
    Route::post('/media/upload', [\App\Http\Controllers\MediaController::class, 'upload'])->name('media.upload');

    Route::get('/admin', [AdminDashboardController::class, 'index'])->middleware('admin')->name('admin.dashboard');
    Route::get('/admin/activity', [AdminDashboardController::class, 'activity'])->middleware('admin')->name('admin.activity');
    Route::resource('admin/users', \App\Http\Controllers\Admin\UserController::class)->middleware('admin')->names('admin.users');
    Route::resource('admin/applications', \App\Http\Controllers\Admin\ApplicationController::class)->middleware('admin')->names('admin.applications');
    Route::post('admin/documents/{document}/archive', [\App\Http\Controllers\Admin\DocumentController::class, 'archive'])->middleware('admin')->name('admin.documents.archive');
    Route::post('admin/documents/{document}/restore', [\App\Http\Controllers\Admin\DocumentController::class, 'restore'])->middleware('admin')->name('admin.documents.restore');
    Route::delete('admin/documents/{document}/force-delete', [\App\Http\Controllers\Admin\DocumentController::class, 'forceDelete'])->middleware('admin')->name('admin.documents.force-delete');
    Route::resource('admin/documents', \App\Http\Controllers\Admin\DocumentController::class)->middleware('admin')->names('admin.documents');
    
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');
});

Route::get('/original', function () {
    return Inertia::render('FrontendOriginal');
});

require __DIR__.'/auth.php';