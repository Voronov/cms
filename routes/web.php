<?php

use App\Http\Controllers\AdminController;
use App\Http\Controllers\Auth\ForgotPasswordController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\Auth\RegisterController;
use App\Http\Controllers\Auth\ResetPasswordController;
use Illuminate\Foundation\Auth\EmailVerificationRequest;
use App\Http\Controllers\ProfileController;
use App\Http\Controllers\PageController;
use App\Http\Controllers\LogController;
use App\Http\Controllers\FormController;
use App\Http\Controllers\FormFrontendController;
use App\Http\Controllers\NavigationItemController;
use App\Http\Controllers\EntityController;
use App\Http\Controllers\EntityApiController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/', [App\Http\Controllers\FrontendPageController::class, 'show']);

// Auth Routes
Route::middleware('guest')->group(function () {
    Route::get('register', [RegisterController::class, 'create'])->name('register');
    Route::post('register', [RegisterController::class, 'store']); // Name defaults to register.store? No, laravel resource naming. Manually handle.

    Route::get('login', [LoginController::class, 'create'])->name('login');
    Route::post('login', [LoginController::class, 'store']);

    Route::get('forgot-password', [ForgotPasswordController::class, 'create'])->name('password.request');
    Route::post('forgot-password', [ForgotPasswordController::class, 'store'])->name('password.email');

    Route::get('reset-password/{token}', [ResetPasswordController::class, 'create'])->name('password.reset');
    Route::post('reset-password', [ResetPasswordController::class, 'store'])->name('password.update');
});

Route::middleware('auth')->group(function () {
    Route::post('logout', [LoginController::class, 'destroy'])->name('logout');

    // Email Verification
    Route::get('/email/verify', function () {
        return view('auth.verify');
    })->middleware('auth')->name('verification.notice');

    Route::get('/email/verify/{id}/{hash}', function (EmailVerificationRequest $request) {
        $request->fulfill();
        return redirect()->route('admin.dashboard');
    })->middleware(['auth', 'signed'])->name('verification.verify');

    Route::post('/email/verification-notification', function (Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('message', 'Verification link sent!');
    })->middleware(['auth', 'throttle:6,1'])->name('verification.send');

    // Approval Wait Page
    Route::get('/approval', function () {
        return view('auth.approval');
    })->name('approval.wait');
});

// Admin Routes (Protected)
Route::middleware(['auth', 'verified', 'approved', 'nocache'])->prefix('admin')->group(function () {
    Route::get('/', [AdminController::class, 'index'])->name('admin.dashboard');
    Route::get('/users', [AdminController::class, 'users'])->name('admin.users');
    Route::post('/users/{id}/approve', [AdminController::class, 'approve'])->name('admin.users.approve');

    // Profile Routes
    Route::get('/profile', [ProfileController::class, 'edit'])->name('admin.profile.edit');
    Route::put('/profile', [ProfileController::class, 'update'])->name('admin.profile.update');
    Route::post('/profile/toggle-theme', [ProfileController::class, 'toggleTheme'])->name('admin.profile.toggle-theme');
    Route::get('/profile/two-factor', function () {
        return view('admin.profile.two-factor');
    })->name('admin.profile.two-factor');

    Route::resource('pages', PageController::class, ['as' => 'admin']);
    Route::post('/pages/{page}/blocks', [\App\Http\Controllers\PageBlockController::class, 'saveBlocks'])->name('admin.pages.blocks.save');
    Route::resource('redirects', \App\Http\Controllers\RedirectController::class, ['as' => 'admin'])->only(['index', 'store', 'destroy']);
    Route::resource('navigation', NavigationItemController::class, ['as' => 'admin'])->except(['show']);

    Route::get('/entities/{type}', [EntityController::class, 'index'])->name('admin.entities.index');
    Route::get('/entities/{type}/create', [EntityController::class, 'create'])->name('admin.entities.create');
    Route::post('/entities/{type}', [EntityController::class, 'store'])->name('admin.entities.store');
    Route::get('/entities/{type}/{entity}/edit', [EntityController::class, 'edit'])->name('admin.entities.edit');
    Route::put('/entities/{type}/{entity}', [EntityController::class, 'update'])->name('admin.entities.update');
    Route::delete('/entities/{type}/{entity}', [EntityController::class, 'destroy'])->name('admin.entities.destroy');

    // Entity API endpoints
    Route::get('/api/entities/types', [EntityApiController::class, 'getEntityTypes'])->name('admin.api.entity-types');
    Route::get('/api/entities/{type}/categories', [EntityApiController::class, 'getCategories'])->name('admin.api.entity-categories');
    Route::get('/api/entities/{type}/items', [EntityApiController::class, 'getEntities'])->name('admin.api.entity-items');
    Route::get('/api/entities/{type}/pagination', [EntityApiController::class, 'getPaginationOptions'])->name('admin.api.entity-pagination');

    Route::post('/media/upload', [\App\Http\Controllers\MediaController::class, 'upload'])->name('admin.media.upload');
    Route::post('/media/crop', [\App\Http\Controllers\MediaController::class, 'crop'])->name('admin.media.crop');
    Route::get('/logs', [LogController::class, 'index'])->name('admin.logs.index');
    Route::resource('forms', FormController::class, ['as' => 'admin'])->parameters([
        'forms' => 'identifier'
    ]);
});

Route::get('/sitemap.xml', [App\Http\Controllers\SitemapController::class, 'index']);

// Public Form Routes
Route::get('/forms/{identifier}', [FormFrontendController::class, 'show'])->name('forms.show');
Route::post('/forms/{identifier}', [FormFrontendController::class, 'submit'])->name('forms.submit');

// Frontend Catch-all Route (Must be last)
Route::get('/{any}', [App\Http\Controllers\FrontendPageController::class, 'show'])->where('any', '.*');
