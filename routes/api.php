<?php

use App\Http\Controllers\RfidLogController;
use App\Http\Controllers\RfidScannerStatusController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/{yard}/rfid-logs', [RfidLogController::class, 'store'])
    ->whereNumber('yard')
    ->middleware(['auth:sanctum', 'abilities:rfid:write']);

// Whether each gate scanner (IN / OUT) is active, relayed by the reader server. Same token as the logs.
Route::post('/v1/{yard}/rfid-scanner-status', [RfidScannerStatusController::class, 'store'])
    ->whereNumber('yard')
    ->middleware(['auth:sanctum', 'abilities:rfid:write']);
