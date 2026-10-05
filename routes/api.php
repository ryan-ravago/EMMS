<?php

use App\Http\Controllers\RfidLogController;
use Illuminate\Support\Facades\Route;

Route::post('/v1/{yard}/rfid-logs', [RfidLogController::class, 'store'])
    ->whereNumber('yard')
    ->middleware(['auth:sanctum', 'abilities:rfid:write']);
