<?php

use App\Http\Controllers\SocialiteController;
use App\Mail\WorkOrderConfirmationMail;
use App\Models\WorkOrder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;

// Route::get('/', function () {
//     return view('welcome');
// });

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

Route::get('/test-aims-db', function () {
    try {
        // Read directly from your .env setting or pass 'sqlsrv_aims'
        $connectionName = env('DB_AIMS_CONNECTION', 'sqlsrv_aims');

        $db = DB::connection($connectionName);
        $db->getPdo();

        return "Connected successfully to [{$connectionName}] -> Database: ".$db->getDatabaseName();
    } catch (Exception $e) {
        return 'Connection failed: '.$e->getMessage();
    }
});
