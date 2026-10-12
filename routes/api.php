<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\RoleController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/v1', function () {
    return response()->json(['message' => 'Welcome to the API version 0.0.1']);
});

Route::get('/v1/health', function () {
    return response()->json(['status' => 'ok']);
});

Route::apiResource('/v1/roles', RoleController::class)->names('api.roles');
Route::apiResource('/v1/categories', \App\Http\Controllers\V1\CategoryController::class)->names('api.categories');
Route::apiResource('/v1/logs', \App\Http\Controllers\V1\LogController::class)->names('api.logs');
Route::apiResource('/v1/notifications', \App\Http\Controllers\V1\NotificationController::class)->names('api.notifications');