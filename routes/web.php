<?php

use App\Http\Controllers\ProfileController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\AssignmentViewController;
use App\Http\Controllers\NotificationController;
use App\Http\Controllers\AIAssistantController;
use App\Http\Controllers\SIAKADSimulatorController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('welcome');
});

// SIAKAD Portal Simulator (for testing SSO)
Route::get('/siakad-demo', [SIAKADSimulatorController::class, 'dashboard'])->name('siakad.simulator');
Route::get('/siakad-demo/generate-link', [SIAKADSimulatorController::class, 'generateLink'])->name('siakad.simulator.link');

Route::get('/dashboard', [DashboardController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::middleware('auth')->group(function () {
    Route::get('/profile', [ProfileController::class, 'edit'])->name('profile.edit');
    Route::patch('/profile', [ProfileController::class, 'update'])->name('profile.update');
    Route::delete('/profile', [ProfileController::class, 'destroy'])->name('profile.destroy');

    // Assignment views
    Route::get('/assignments', [AssignmentViewController::class, 'index'])->name('assignments.index');
    Route::get('/assignments/create', [AssignmentViewController::class, 'create'])->name('assignments.create');
    Route::post('/assignments', [AssignmentViewController::class, 'store'])->name('assignments.store');
    Route::get('/assignments/{assignment}', [AssignmentViewController::class, 'show'])->name('assignments.show');
    Route::post('/assignments/{assignment}/submit', [AssignmentViewController::class, 'submit'])->name('assignments.submit');

    // Notifications
    Route::get('/notifications', [NotificationController::class, 'index'])->name('notifications.index');
    Route::get('/notifications/unread', [NotificationController::class, 'unread'])->name('notifications.unread');
    Route::post('/notifications/{notification}/read', [NotificationController::class, 'markAsRead'])->name('notifications.read');
    Route::post('/notifications/read-all', [NotificationController::class, 'markAllAsRead'])->name('notifications.readAll');
    Route::delete('/notifications/{notification}', [NotificationController::class, 'destroy'])->name('notifications.destroy');

    // AI Assistant
    Route::get('/ai-assistant', [AIAssistantController::class, 'index'])->name('ai.assistant');
    Route::get('/ai/recommendations', [AIAssistantController::class, 'recommendations'])->name('ai.recommendations');
    Route::get('/ai/study-plan', [AIAssistantController::class, 'studyPlan'])->name('ai.studyPlan');
    Route::post('/ai/chat', [AIAssistantController::class, 'chat'])->name('ai.chat');
    Route::get('/ai/insights', [AIAssistantController::class, 'insights'])->name('ai.insights');
});

require __DIR__.'/auth.php';
