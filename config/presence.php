<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Who is online
    |--------------------------------------------------------------------------
    |
    | Super admins can see on the Users page whether a user is online and when they were last active.
    | See App\Http\Middleware\TrackUserActivity.
    |
    | online_seconds       A user is online while their last request is not older than this. An open page asks
    |                      for its notifications every 30 seconds, so the default of 2 minutes allows for a
    |                      couple of missed refreshes (a browser slows down tabs that are in the background).
    | write_every_seconds  At most one database write per user per this many seconds, however many requests they make.
    |
    */

    'online_seconds' => (int) env('PRESENCE_ONLINE_SECONDS', 120),
    'write_every_seconds' => (int) env('PRESENCE_WRITE_EVERY_SECONDS', 30),
];
