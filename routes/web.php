<?php
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\{
    AuthController,
    DashboardController,
    PesertaController,
    KegiatanController,
    KehadiranController,
    LaporanController
};

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

    // PENTING: parameter harus eksplisit. Tanpa ini Laravel pluralisasi "peserta"
    // menjadi "pesertum" sehingga route model binding gagal dan update/delete
    // diam-diam tidak mengubah apa pun.
    Route::resource('peserta', PesertaController::class)->parameters(['peserta' => 'peserta']);
    Route::resource('kegiatan', KegiatanController::class)->parameters(['kegiatan' => 'kegiatan']);
    Route::resource('kehadiran', KehadiranController::class)->parameters(['kehadiran' => 'kehadiran']);

    Route::get('laporan', [LaporanController::class, 'index'])->name('laporan.index');
});
