<?php

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\V1\RoleController;
use App\Http\Controllers\V1\CategoryController;
use App\Http\Controllers\V1\LogController;
use App\Http\Controllers\V1\NotificationController;
use App\Http\Controllers\V1\UniversityController;
use App\Http\Controllers\V1\PalmaresController;
use App\Http\Controllers\V1\GraduateController;
use App\Http\Controllers\V1\ProfileSearchController;
use App\Http\Controllers\V1\TicketController;
use App\Http\Controllers\V1\UserController;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::get('/v1', function () {
    return response()->json(['message' => 'Welcome to the API version 0.0.1']);
});

Route::get('/v1/health', function () {
    return response()->json(['message' => 'ok']);
});

Route::apiResource('/v1/roles', RoleController::class)->names('api.roles');
Route::apiResource('/v1/categories', CategoryController::class)->names('api.categories');
Route::apiResource('/v1/logs', LogController::class)->names('api.logs');
Route::apiResource('/v1/notifications', NotificationController::class)->names('api.notifications');
Route::apiResource('/v1/universities', UniversityController::class)->names('api.universities');
Route::apiResource('/v1/palmares',PalmaresController::class)->names('api.palmares');
Route::apiResource('/v1/graduates',GraduateController::class)->names('api.graduates');
Route::apiResource('/v1/tickets',TicketController::class)->names('api.tickets');
Route::apiResource('/v1/profiles',ProfileSearchController::class)->names('api.profiles');
Route::apiResource('/v1/users',UserController::class)->names('api.users');