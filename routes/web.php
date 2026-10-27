<?php

use App\Http\Controllers\V1\AccountController;
use App\Http\Controllers\V1\AdminController;
use App\Http\Controllers\V1\AuthController;
use App\Http\Controllers\V1\RhController;
use App\Http\Controllers\V1\UniversityController;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return view('index');
})->name('home');

Route::prefix('auth')->name('auth.')->controller(AuthController::class)->group(function () {
    Route::middleware('guest')->group(function () {
        Route::get('/login', 'index')->name('login');
        Route::post('/login', 'login')->name('login.submit');
        Route::get('/forgot-password', 'forgotPassword')->name('forgot');
    });

    Route::middleware('auth')->group(function () {
        Route::post('/logout', 'logout')->name('logout');
    });
});

Route::middleware('auth')->group(function () {
    Route::get('/account', [AccountController::class, 'edit'])->name('account.edit');
    Route::put('/account', [AccountController::class, 'update'])->name('account.update');

    Route::prefix('admin')->name('admin.')->middleware('role:admin')->controller(AdminController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/users/{user}/toggle', 'toggleUser')->name('users.toggle');
        Route::post('/categories', 'storeCategory')->name('categories.store');
        Route::post('/universities', 'storeUniversity')->name('universities.store');
        Route::post('/roles', 'storeRole')->name('roles.store');
        Route::delete('/roles/{role}', 'destroyRole')->name('roles.destroy');
    });

    Route::prefix('rh')->name('rh.')->middleware('role:rh')->controller(RhController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::post('/search', 'search')->name('search');
        Route::post('/tickets', 'buyTickets')->name('tickets.buy');
        Route::get('/profile', 'profile')->name('profile');
        Route::put('/profile', 'updateProfile')->name('profile.update');
    });

    Route::prefix('university')->name('university.')->middleware('role:university,universite')->controller(UniversityController::class)->group(function () {
        Route::get('/', 'index')->name('index');
        Route::put('/profile', 'updateProfile')->name('profile.update');
        Route::post('/palmares/import', 'importPalmares')->name('palmares.import');
    });
});
