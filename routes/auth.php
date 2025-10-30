<?php

use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\Auth\SIAKADAuthController;
use App\Http\Controllers\Auth\SIAKADSSOController;
use App\Http\Controllers\Auth\ConfirmablePasswordController;
use App\Http\Controllers\Auth\EmailVerificationNotificationController;
use App\Http\Controllers\Auth\EmailVerificationPromptController;
use App\Http\Controllers\Auth\NewPasswordController;
use App\Http\Controllers\Auth\PasswordController;
use App\Http\Controllers\Auth\PasswordResetLinkController;
use App\Http\Controllers\Auth\RegisteredUserController;
use App\Http\Controllers\Auth\VerifyEmailController;
use Illuminate\Support\Facades\Route;

// SIAKAD SSO Login Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])
        ->name('login');

    Route::post('login/siakad', [SIAKADAuthController::class, 'login'])
        ->name('siakad.login');

    Route::get('login/standard', function () {
        return view('auth.login-standard');
    })->name('login.standard');

    Route::post('login/standard', [AuthenticatedSessionController::class, 'store'])
        ->name('login.standard.post');

    // SIAKAD SSO Callback (from SIAKAD portal)
    Route::get('auth/siakad/callback', [SIAKADSSOController::class, 'callback'])
        ->name('siakad.sso.callback');
    
    // SIAKAD SSO Redirect (to SIAKAD portal)
    Route::get('auth/siakad/redirect', [SIAKADSSOController::class, 'redirect'])
        ->name('siakad.sso.redirect');
    
    // Test SSO Token Generator (local only)
    Route::get('auth/siakad/test-token', [SIAKADSSOController::class, 'generateTestToken'])
        ->name('siakad.sso.test');

    Route::get('register', [RegisteredUserController::class, 'create'])
        ->name('register');

    Route::post('register', [RegisteredUserController::class, 'store']);

    Route::get('forgot-password', [PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [PasswordResetLinkController::class, 'store'])
        ->name('password.email');

    Route::get('reset-password/{token}', [NewPasswordController::class, 'create'])
        ->name('password.reset');

    Route::post('reset-password', [NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware('auth')->group(function () {
    Route::get('verify-email', EmailVerificationPromptController::class)
        ->name('verification.notice');

    Route::get('verify-email/{id}/{hash}', VerifyEmailController::class)
        ->middleware(['signed', 'throttle:6,1'])
        ->name('verification.verify');

    Route::post('email/verification-notification', [EmailVerificationNotificationController::class, 'store'])
        ->middleware('throttle:6,1')
        ->name('verification.send');

    Route::get('confirm-password', [ConfirmablePasswordController::class, 'show'])
        ->name('password.confirm');

    Route::post('confirm-password', [ConfirmablePasswordController::class, 'store']);

    Route::put('password', [PasswordController::class, 'update'])->name('password.update');

    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])
        ->name('logout');
});
