<?php

use App\Http\Controllers\Admin\AdminDashboardController;
use App\Http\Controllers\Admin\UniversityController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\PageController;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| Public routes
|--------------------------------------------------------------------------
*/

Route::get('/', [PageController::class, 'home'])->name('home');
Route::get('/about', [PageController::class, 'about'])->name('about');
Route::get('/health', [PageController::class, 'health'])->name('health');

/*
|--------------------------------------------------------------------------
| Guest routes (registration & login)
|--------------------------------------------------------------------------
*/

Route::middleware('guest')->group(function () {
    Route::get('/register', [RegisteredUserController::class, 'create'])->name('register');
    Route::post('/register', [RegisteredUserController::class, 'store']);

    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store']);
});

/*
|--------------------------------------------------------------------------
| Authenticated routes
|--------------------------------------------------------------------------
*/

Route::middleware('auth')->group(function () {
    // Logout changes state, so it must be POST only — never a GET route.
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');

    Route::get('/dashboard', [PageController::class, 'dashboard'])->name('dashboard');
    Route::get('/profile', [PageController::class, 'profile'])->name('profile');
});

/*
|--------------------------------------------------------------------------
| Admin routes (authenticated + admin role required)
|--------------------------------------------------------------------------
| Every route below is protected server-side by the `admin` middleware (see
| App\Http\Middleware\EnsureUserIsAdmin). Hiding navigation links is a
| usability convenience only and is never relied on for authorization.
*/

Route::middleware(['auth', 'admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/', AdminDashboardController::class)->name('dashboard');

    // Delete confirmation is a GET screen; the destructive call is a DELETE.
    Route::get('/universities/{university}/delete', [UniversityController::class, 'confirmDelete'])
        ->name('universities.confirm-delete');

    Route::resource('universities', UniversityController::class);
});
