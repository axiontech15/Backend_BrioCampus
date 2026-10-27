<?php

use App\Http\Controllers\V1\PalmaresController;
use Illuminate\Support\Facades\Route;

Route::get('/v1/health', function () {
    return response()->json(['message' => 'ok']);
});

Route::middleware('auth:sanctum')->prefix('v1')->group(function () {
    Route::apiResource('palmares', PalmaresController::class)->names('api.palmares');
});
