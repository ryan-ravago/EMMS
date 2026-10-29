<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Movement pairing window
    |--------------------------------------------------------------------------
    |
    | Equipment carries several RFID tags, and a reader may scan them one after another as the
    | equipment passes. Scans of the same equipment closer together than this many seconds belong
    | to one trip instead of starting a new one.
    |
    */

    'movement_window_seconds' => (int) env('RFID_MOVEMENT_WINDOW_SECONDS', 300),

];
