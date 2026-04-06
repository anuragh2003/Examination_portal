<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;
use App\Http\Controllers\StudentController;

Route::prefix('auth')->group(function () {
    
    Route::post('login', [UserController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        
        Route::post('logout', [UserController::class, 'logout']);
        
    });
});

// Test endpoint for screenshot upload
Route::post('exam/test-upload-endpoint/{uuid}', function($uuid) {
    \Illuminate\Support\Facades\Log::info('API Test endpoint reached', ['uuid' => $uuid]);
    return response()->json(['success' => true, 'message' => 'Server is responding via API']);
})->name('api.test.upload.endpoint');

Route::post('exam/{uuid}/upload-proctor-screenshots', [StudentController::class, 'uploadProctorScreenshots'])
    ->name('api.upload.proctor.screenshots');
?>