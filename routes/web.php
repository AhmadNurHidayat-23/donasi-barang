<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\DonationController;
use Illuminate\Support\Facades\Route;

Route::get('/', [DonationController::class, 'index'])->name('home');
Route::get('/masuk', [AuthController::class, 'form'])->middleware('guest')->name('login');
Route::post('/masuk', [AuthController::class, 'login'])->middleware(['guest', 'throttle:5,1']);
Route::post('/daftar', [AuthController::class, 'register'])->middleware(['guest', 'throttle:5,1']);
Route::middleware('auth')->group(function () {
    Route::post('/keluar', [AuthController::class, 'logout']);
    Route::get('/donasi/baru', [DonationController::class, 'create']);
    Route::post('/donasi', [DonationController::class, 'store'])->middleware('throttle:10,1');
    Route::get('/riwayat', [DonationController::class, 'history']);
    Route::post('/donasi/{donation}/ajukan', [DonationController::class, 'apply'])->middleware('throttle:10,1');
    Route::patch('/donasi/{donation}/pengajuan/{id}', [DonationController::class, 'review']);
    Route::post('/donasi/{donation}/pengambilan', [DonationController::class, 'pickup']);
    Route::post('/donasi/{donation}/serah-terima', [DonationController::class, 'handover']);
    Route::post('/donasi/{donation}/buka-kembali', [DonationController::class, 'reopen']);
});
Route::get('/donasi/{donation}', [DonationController::class, 'show'])->name('donation.show');
