<?php

use Illuminate\Support\Facades\Route;
use App\Http\Controllers\SocialiteController;

// Route::get('/', function () {
//     return view('welcome');
// });


// Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
//     ->name('socialite.redirect');
// Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])
//     ->name('socialite.callback');
Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])->name('auth.google');
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback'])->name('auth.google.callback');
