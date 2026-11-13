<?php

use App\Http\Controllers\AssetMovementPrintController;
use App\Http\Controllers\PwaManifestController;
use App\Http\Controllers\SocialiteController;
use App\Mail\WorkOrderConfirmationMail;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

// Lets phones and browsers offer "Install app" (see also public/sw.js and the head hook in AdminPanelProvider).
Route::get('/manifest.webmanifest', PwaManifestController::class)->name('pwa.manifest');

// Route::get('/auth/{provider}/redirect', [SocialiteController::class, 'redirect'])
//     ->name('socialite.redirect');
// Route::get('/auth/{provider}/callback', [SocialiteController::class, 'callback'])
//     ->name('socialite.callback');
Route::get('/auth/google', [SocialiteController::class, 'redirectToGoogle'])
    ->name('auth.google')
    ->middleware('throttle:google-auth');
Route::get('/auth/google/callback', [SocialiteController::class, 'handleGoogleCallback'])
    ->name('auth.google.callback')
    ->middleware('throttle:google-auth');

// Route::get('/wo-preview-confirmation', function () {
//     $workOrder = WorkOrder::with(['priority', 'createdBy'])->first();

//     return new WorkOrderConfirmationMail($workOrder);
// });

// Printable copy of a verified equipment trip (Time In / Out). The controller checks the sign-in, the "Print"
// permission and the trip's state; the limit keeps repeated PDF rendering from loading the server.
Route::get('/asset-movements/{assetMovement}/print', AssetMovementPrintController::class)
    ->name('asset-movements.print')
    ->middleware('throttle:30,1');
