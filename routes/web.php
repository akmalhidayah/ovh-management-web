<?php

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\User\DashboardController as UserDashboardController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    if (! auth()->check()) {
        return redirect()->route('login');
    }

    return auth()->user()->usertype === 'admin'
        ? redirect()->route('admin.dashboard')
        : redirect()->route('user.dashboard');
});

Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login'])->name('login.attempt');
});

Route::post('/logout', [LoginController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

Route::middleware(['auth', 'usertype:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/overview', [AdminDashboardController::class, 'overview'])->name('overview');
    Route::get('/procurement', [AdminDashboardController::class, 'procurement'])->name('procurement');
    Route::get('/kalender-overhaul', [AdminDashboardController::class, 'kalenderOverhaul'])->name('kalender-overhaul');
    Route::get('/schedule', [AdminDashboardController::class, 'schedule'])->name('schedule');
    Route::get('/commissioning', [AdminDashboardController::class, 'commissioning'])->name('commissioning');
    Route::get('/qc', [AdminDashboardController::class, 'qc'])->name('qc');
    Route::get('/equipment', [AdminDashboardController::class, 'equipment'])->name('equipment');
    Route::get('/mom', [AdminDashboardController::class, 'mom'])->name('mom');
    Route::get('/dokument', [AdminDashboardController::class, 'dokument'])->name('dokument');
    Route::get('/master-data', [AdminDashboardController::class, 'masterData'])->name('master-data');
});

Route::middleware(['auth', 'usertype:user'])->prefix('user')->name('user.')->group(function () {
    Route::get('/dashboard', [UserDashboardController::class, 'dashboard'])->name('dashboard');
    Route::get('/overview', [UserDashboardController::class, 'overview'])->name('overview');
    Route::get('/procurement', [UserDashboardController::class, 'procurement'])->name('procurement');
    Route::get('/kalender-overhaul', [UserDashboardController::class, 'kalenderOverhaul'])->name('kalender-overhaul');
    Route::get('/schedule', [UserDashboardController::class, 'schedule'])->name('schedule');
    Route::get('/commissioning', [UserDashboardController::class, 'commissioning'])->name('commissioning');
    Route::get('/qc', [UserDashboardController::class, 'qc'])->name('qc');
    Route::get('/equipment', [UserDashboardController::class, 'equipment'])->name('equipment');
    Route::get('/mom', [UserDashboardController::class, 'mom'])->name('mom');
    Route::get('/dokument', [UserDashboardController::class, 'dokument'])->name('dokument');
});
