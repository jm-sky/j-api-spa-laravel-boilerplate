<?php

use DevMadeIt\Http\Controllers\Auth\SocialiteAuthController;
use DevMadeIt\Http\Controllers\ProfileController;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Route;

/*
|--------------------------------------------------------------------------
| API Routes
|--------------------------------------------------------------------------
|
| Here is where you can register API routes for your application. These
| routes are loaded by the RouteServiceProvider within a group which
| is assigned the "api" middleware group. Enjoy building your API!
|
*/

Route::controller(SocialiteAuthController::class)->group(function (): void {
    Route::get('/oauth/redirect/{driver}', 'redirect')->name('socialite.redirect');
    Route::get('/oauth/{driver}/callback', 'callback')->name('socialite.callback');
});

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/user', function (Request $request) {
        return $request->user();
    });

    Route::controller(ProfileController::class)->group(function (): void {
        Route::get('/profile', 'edit')->name('profile.edit');
        Route::patch('/profile', 'update')->name('profile.update');
        Route::delete('/profile', 'destroy')->name('profile.destroy');
    });
});

require __DIR__.'/auth.php';
