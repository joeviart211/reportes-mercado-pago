<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\MlAuthController;
use App\Http\Controllers\MpReportController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\RoleController;

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

     Route::get('/mp/export/{branch}/{fileName}', [MpReportController::class, 'exportCsv'])->name('exportCsv');
     Route::get('/mp/export/{branch}/{fileName}', [MpReportController::class, 'exportXls'])->name('exportXls');

     Route::resource('users', UserController::class);
});

// ── Callback ML — fuera de auth porque ML redirige sin sesión activa ──
Route::get('ml/callback', [MlAuthController::class, 'callback'])
     ->name('ml.callback');

Route::middleware(['auth'])->prefix('users')->name('users.')->group(function () {  
         Route::put('/{user}/roles',       [UserController::class, 'syncRoles'])       ->name('roles.sync');
    Route::put('/{user}/permissions', [UserController::class, 'syncPermissions']) ->name('permissions.sync');
});
Route::middleware(['auth'])->prefix('roles')->name('roles.')->group(function () {

    Route::get('/',            [RoleController::class, 'index'])   ->name('index');
    Route::get('/create',      [RoleController::class, 'create'])  ->name('create');
    Route::post('/',           [RoleController::class, 'store'])   ->name('store');
    Route::get('/{role}/edit', [RoleController::class, 'edit'])    ->name('edit');
    Route::put('/{role}',      [RoleController::class, 'update'])  ->name('update');
    Route::delete('/{role}',   [RoleController::class, 'destroy']) ->name('destroy');
});   

require __DIR__.'/auth.php';