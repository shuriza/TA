<?php

use App\Http\Controllers\Api\AssignmentController;
use App\Http\Controllers\Api\SubmissionController;
use App\Http\Controllers\Api\CourseController;
use App\Http\Controllers\Api\MaterialController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::middleware('auth:sanctum')->group(function () {
    
    // Assignments API
    Route::apiResource('assignments', AssignmentController::class)->names([
        'index' => 'api.assignments.index',
        'store' => 'api.assignments.store',
        'show' => 'api.assignments.show',
        'update' => 'api.assignments.update',
        'destroy' => 'api.assignments.destroy',
    ]);
    Route::get('assignments-summary', [AssignmentController::class, 'summary'])->name('api.assignments.summary');
    
    // Submissions API
    Route::apiResource('submissions', SubmissionController::class)->names([
        'index' => 'api.submissions.index',
        'store' => 'api.submissions.store',
        'show' => 'api.submissions.show',
        'update' => 'api.submissions.update',
        'destroy' => 'api.submissions.destroy',
    ]);
    Route::post('submissions/{submission}/grade', [SubmissionController::class, 'grade'])->name('api.submissions.grade');
    
    // Courses API
    Route::apiResource('courses', CourseController::class)->names([
        'index' => 'api.courses.index',
        'store' => 'api.courses.store',
        'show' => 'api.courses.show',
        'update' => 'api.courses.update',
        'destroy' => 'api.courses.destroy',
    ]);
    
    // Materials API
    Route::apiResource('materials', MaterialController::class)->names([
        'index' => 'api.materials.index',
        'store' => 'api.materials.store',
        'show' => 'api.materials.show',
        'update' => 'api.materials.update',
        'destroy' => 'api.materials.destroy',
    ]);
    
});
