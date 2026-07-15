<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PelanggaranController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AdminController;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\DashboardController;

// Guest Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', [LoginController::class, 'showLoginForm'])->name('login');
    Route::post('/login', [LoginController::class, 'login']);
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::post('/logout', [LoginController::class, 'logout'])->name('logout');

    Route::get('/', [DashboardController::class, 'index']);

    // Pelanggaran (Violation logging)
    Route::get('/pelanggaran', [PelanggaranController::class, 'create']);
    Route::post('/pelanggaran', [PelanggaranController::class, 'store']);

    // Laporan (Monthly reports & workflow)
    Route::get('/laporan', [LaporanController::class, 'index']);
    Route::post('/laporan/{id}/sign', [LaporanController::class, 'sign']);

    // Admin Routes
    Route::get('/admin/users', [AdminController::class, 'index']);
    Route::post('/admin/users', [AdminController::class, 'store']);
    Route::post('/admin/users/{id}/toggle-status', [AdminController::class, 'toggleStatus']);
});
