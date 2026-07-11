<?php

use App\Http\Controllers\FirmwareController;
use App\Http\Controllers\GoogleAuthController;
use App\Http\Controllers\DebugController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return redirect('/admin');
});

Route::get('/auth/google', [GoogleAuthController::class, 'redirect'])->name('google.redirect');
Route::get('/auth/google/callback', [GoogleAuthController::class, 'callback'])->name('google.callback');

Route::get('/debug/auth', [DebugController::class, 'index'])->name('debug.auth');

Route::get('/firmware/{firmware}/download', [FirmwareController::class, 'download'])->name('firmware.download');
