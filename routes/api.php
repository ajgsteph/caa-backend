<?php

use App\Enums\UserRole;
use App\Http\Controllers\Auth\AuthController;
use App\Http\Controllers\Auth\ProfileController;
use App\Http\Controllers\CertificateController;
use App\Http\Controllers\DownloadController;
use App\Http\Controllers\PublicVerificationController;
use Illuminate\Support\Facades\Route;

Route::prefix('v1')->group(function (): void {
    // ---- Public ---------------------------------------------------------
    Route::post('auth/register', [AuthController::class, 'register']);
    Route::post('auth/login', [AuthController::class, 'login']);

    Route::get('verify/{number}', [PublicVerificationController::class, 'show'])
        ->where('number', 'CAA-\d{4}-\d+');

    Route::get('download/{certificate}', [DownloadController::class, 'show'])
        ->middleware('signed')
        ->name('download.show');

    // ---- Authenticated --------------------------------------------------
    Route::middleware(['auth:sanctum', 'artist.active'])->group(function (): void {
        Route::post('auth/logout', [AuthController::class, 'logout']);

        Route::prefix('profile')->group(function (): void {
            Route::get('/', [ProfileController::class, 'show']);
            Route::patch('password', [ProfileController::class, 'updatePassword']);
            Route::patch('email', [ProfileController::class, 'updateEmail']);
            Route::patch('phone', [ProfileController::class, 'updatePhone']);
        });

        // Artist endpoints
        Route::middleware('role:'.UserRole::ARTIST->value)->group(function (): void {
            Route::get('certificates', [CertificateController::class, 'index']);
            Route::post('certificates', [CertificateController::class, 'store']);
            Route::get('certificates/{certificate}', [CertificateController::class, 'show']);
            Route::get('certificates/{certificate}/download-link', [CertificateController::class, 'downloadLink']);
        });

        // Admin endpoints
        Route::middleware('role:'.UserRole::ADMIN->value.'|'.UserRole::SUPER_ADMIN->value)->group(function (): void {
            Route::post('certificates/{certificate}/revoke', [CertificateController::class, 'revoke']);
        });
    });
});
