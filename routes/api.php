<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\PeminjamanRuangController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\AdminAuthController;
use App\Http\Controllers\DaftarBarangController;
use App\Http\Controllers\DaftarRuangController;
use App\Http\Controllers\DashboardAdminController;
use App\Http\Controllers\PeminjamanBarangController;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider and all of them will
| be assigned to the "api" middleware group. Make something great!
|
*/

Route::prefix('admin')->group(function () {
    Route::post('/login', [AdminAuthController::class, 'login'])->name('login');

    Route::middleware(['auth:sanctum', 'role:admin'])->group(function () {
        Route::post('/logout', [AdminAuthController::class, 'logout']);
        Route::get('/barang', [DaftarBarangController::class, 'index']);
        Route::get('/ruang', [DaftarRuangController::class, 'index']);
        Route::get('/barang/count', [DaftarBarangController::class, 'countBarang']);
        Route::get('/ruang/count', [DaftarRuangController::class, 'countRuang']);
        Route::get('/users', [DashboardAdminController::class, 'index']);
        Route::get('/barang-dipinjam', [DashboardAdminController::class, 'allBarangDipinjam']);
        Route::get('/ruang-dipinjam', [DashboardAdminController::class, 'allRuangDipinjam']);
        Route::get('/barang/{id}', [DaftarBarangController::class, 'getBarangById']);
        Route::post('/barang/store', [DaftarBarangController::class, 'store']);
        Route::post('/ruang/store', [DaftarRuangController::class, 'store']);
        Route::get('/ruang/{id}', [DaftarRuangController::class, 'getRuangById']);
        Route::put('/approve-return-ruang/{id}', [PeminjamanRuangController::class, 'approveRejectReturnRuang']);
        Route::put('/approve-return-barang/{id}', [PeminjamanBarangController::class, 'approveRejectReturnBarang']);
        Route::put('/approve-reject-peminjaman-ruang/{id}', [PeminjamanRuangController::class, 'approveRejectPeminjamanRuang']);
        Route::put('/approve-reject-peminjaman-barang/{id}', [PeminjamanBarangController::class, 'approveRejectPeminjamanBarang']);
    });
});


Route::prefix('user')->group(function () {
    Route::post('/register', [AuthController::class, 'register']);
    Route::post('/login', [AuthController::class, 'login'])->name('login');

    Route::middleware(['auth:sanctum', 'role:user'])->group(function () {
        Route::post('/logout', [AuthController::class, 'logout']);
        Route::get('/barang', [DaftarBarangController::class, 'index']);
        Route::get('/ruang', [DaftarRuangController::class, 'index']);
        Route::get('/barang/{id}', [DaftarBarangController::class, 'getBarangById']);
        Route::get('/ruang/{id}', [DaftarRuangController::class, 'getRuangById']);
        Route::post('/pinjam-ruang', [PeminjamanRuangController::class, 'create']);
        Route::post('/pinjam-barang', [PeminjamanBarangController::class, 'create']);
        Route::put('/pinjam-barang/{id}/request-return', [PeminjamanBarangController::class, 'requestReturnBarang']);
        Route::put('/pinjam-ruang/{id}/request-return', [PeminjamanRuangController::class, 'requestReturnRuang']);
        Route::get('/history/barang', [PeminjamanBarangController::class, 'historyPeminjamanBarang']);
        Route::get('/rekapitulasi/barang', [PeminjamanBarangController::class, 'historyPeminjamanBarangReturned']);
        Route::get('/history/ruang', [PeminjamanRuangController::class, 'historyPeminjamanRuang']);
        Route::get('/rekapitulasi/ruang', [PeminjamanRuangController::class, 'historyPeminjamanRuangReturned']);
    });
});
