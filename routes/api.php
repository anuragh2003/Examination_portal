<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\UserController;

Route::prefix('auth')->group(function () {
    
    Route::post('login', [UserController::class, 'login']);

    Route::middleware('auth:api')->group(function () {
        
        Route::post('logout', [UserController::class, 'logout']);
        
    });
});
?>