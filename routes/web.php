<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\IbuHamilController;
use App\Http\Controllers\KegiatanController;
use App\Http\Controllers\KunjunganAncController;
use App\Http\Controllers\LaporanController;
use Illuminate\Support\Facades\Route;

/* ── Autentikasi (hanya untuk tamu) ─────────────────── */
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthController::class, 'showLogin'])->name('login');
    Route::post('login', [AuthController::class, 'login'])->name('login.attempt');
});

Route::post('logout', [AuthController::class, 'logout'])
    ->middleware('auth')
    ->name('logout');

/* ── Aplikasi (wajib masuk) ─────────────────────────── */
Route::middleware('auth')->group(function () {
    Route::get('/', [DashboardController::class, 'index'])->name('dashboard');

    // Nama parameter dibuat eksplisit agar route model binding tidak salah tebak.
    Route::resource('ibu-hamil', IbuHamilController::class)
        ->parameters(['ibu-hamil' => 'ibu'])
        ->except(['create', 'edit']);

    Route::resource('kegiatan', KegiatanController::class)
        ->parameters(['kegiatan' => 'kegiatan'])
        ->except(['create', 'edit', 'show']);

    Route::resource('kunjungan', KunjunganAncController::class)
        ->parameters(['kunjungan' => 'kunjungan'])
        ->except(['create', 'edit', 'show']);

    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
});
