<?php

use Illuminate\Support\Facades\Route;
use Inertia\Inertia;
use App\Http\Controllers\Auth\LoginController;
use App\Http\Controllers\PelanggaranController;
use App\Http\Controllers\LaporanController;
use App\Http\Controllers\AdminController;
use App\Http\Controllers\JadwalController;
use App\Http\Controllers\AnggotaController;
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

    // Anggota Routes
    Route::get('/anggota', [AnggotaController::class, 'index']);
    Route::get('/anggota/{id}', [AnggotaController::class, 'show']);

    // Pelanggaran (Violation logging)
    Route::get('/pelanggaran', [PelanggaranController::class, 'create']);
    Route::post('/pelanggaran', [PelanggaranController::class, 'store']);
    Route::get('/pelanggaran/daftar', [PelanggaranController::class, 'index']);
    Route::patch('/pelanggaran/{id}/tindak-lanjut', [PelanggaranController::class, 'updateTindakLanjut']);

    // Laporan (Monthly & Weekly reports & workflow)
    Route::get('/laporan/mingguan', [LaporanController::class, 'mingguan']);
    Route::get('/laporan/bulanan', [LaporanController::class, 'bulanan']);
    Route::post('/laporan/{id}/sign', [LaporanController::class, 'sign']);
    Route::post('/laporan/mingguan/{id}/sign', [LaporanController::class, 'signMingguan']);

    // Admin Routes
    Route::get('/admin/users', [AdminController::class, 'index']);
    Route::post('/admin/users', [AdminController::class, 'store']);
    Route::put('/admin/users/{id}', [AdminController::class, 'update']);
    Route::delete('/admin/users/{id}', [AdminController::class, 'destroy']);
    Route::post('/admin/users/{id}/toggle-status', [AdminController::class, 'toggleStatus']);
    Route::post('/admin/regus', [AdminController::class, 'storeRegu']);

    // Jadwal
    Route::get('/jadwal/manage', [JadwalController::class, 'manage'])->name('jadwal.manage');
    Route::post('/jadwal/manage', [JadwalController::class, 'store'])->name('jadwal.store');
    
    // Exports
    Route::get('/export/jadwal-mingguan', [JadwalController::class, 'exportMingguan'])->name('export.jadwal.mingguan');
    Route::get('/export/jadwal-bulanan', [JadwalController::class, 'exportBulanan'])->name('export.jadwal.bulanan');
    Route::get('/export/laporan', [LaporanController::class, 'exportLaporan'])->name('export.laporan');
    Route::get('/export/laporan-bulanan', [LaporanController::class, 'exportLaporanBulanan'])->name('export.laporan.bulanan');
});
