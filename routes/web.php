<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\MlAuthController;
use App\Http\Controllers\MpReportController;

Route::view('/', 'welcome');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::middleware(['auth'])->group(function () {

    // ── Sucursales ─────────────────────────────────────────────────
    Route::resource('branches', BranchController::class);

    // ── Conexión ML ────────────────────────────────────────────────
    Route::get('branches/{branch}/ml/connect', [MlAuthController::class, 'redirect'])
         ->name('branches.ml.connect');

    // ── Desconexión ML/MP ──────────────────────────────────────────
    Route::delete('branches/{branch}/ml/disconnect', [BranchController::class, 'disconnectMl'])
         ->name('branches.ml.disconnect');

    Route::delete('branches/{branch}/mp/disconnect', [BranchController::class, 'disconnectMp'])
         ->name('branches.mp.disconnect');

    // ── Reportes por sucursal ──────────────────────────────────────
    Route::prefix('branches/{branch}/reports')
         ->name('branches.reports.')
         ->group(function () {
             Route::get('/',                      [MpReportController::class, 'index'])->name('index');
             Route::post('/request',              [MpReportController::class, 'request'])->name('request');
             Route::post('/{fileName}/import',    [MpReportController::class, 'import'])->name('import');
         });
});

// ── Callback ML — fuera de auth porque ML redirige sin sesión activa ──
Route::get('ml/callback', [MlAuthController::class, 'callback'])
     ->name('ml.callback');

require __DIR__.'/auth.php';