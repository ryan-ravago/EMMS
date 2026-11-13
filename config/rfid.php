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

    /*
    |--------------------------------------------------------------------------
    | Time out / time in emails
    |--------------------------------------------------------------------------
    |
    | Each time an equipment or service vehicle goes out or comes in, the asset admins and asset managers are
    | emailed once (not once per RFID tag). Each person can switch it off on My Profile.
    |
    | enabled          Master switch. Keep it off wherever real people must not be emailed (local, testing).
    | max_age_minutes  Events older than this are never emailed, so importing or re-building old trips stays quiet.
    |
    */

    'movement_emails' => [
        'enabled' => (bool) env('RFID_MOVEMENT_EMAILS_ENABLED', true),
        'max_age_minutes' => (int) env('RFID_MOVEMENT_EMAIL_MAX_AGE_MINUTES', 180),
    ],

    /*
    |--------------------------------------------------------------------------
    | Raw reader log
    |--------------------------------------------------------------------------
    |
    | Every read the reader server sends is first stored in rfid_raw_logs (known tag or not). A background job
    | then moves the reads of registered tags into asset_tag_logs and the equipment trips.
    |
    | raw_log_retention_days  Days to keep raw rows (default 7, at least 1). Older rows are deleted by the daily
    |                         `model:prune` run, handled or not. Set with RFID_RAW_LOG_RETENTION_DAYS in .env.
    | process_chunk           Raw rows handled per query batch.
    | stuck_after_minutes     Raw rows waiting longer than this mean processing is not running (health alert).
    |
    */

    'raw_log_retention_days' => (int) env('RFID_RAW_LOG_RETENTION_DAYS', 7),
    'process_chunk' => (int) env('RFID_PROCESS_CHUNK', 1000),
    'stuck_after_minutes' => (int) env('RFID_STUCK_AFTER_MINUTES', 15),

    /*
    |--------------------------------------------------------------------------
    | Scanner status
    |--------------------------------------------------------------------------
    |
    | The reader server reports whether each gate scanner (IN / OUT) is active, on every change and about every
    | 5 minutes (POST /v1/{yard}/rfid-scanner-status).
    |
    | scanner_stale_minutes        No report for this long: the scanner shows as "No report" instead of its last status.
    | scanner_log_retention_days   Days to keep the history of status changes (at least 1).
    | alerts.scanner_inactive_minutes  A scanner must stay inactive this long before people are alerted.
    |
    */

    'scanner_stale_minutes' => (int) env('RFID_SCANNER_STALE_MINUTES', 15),
    'scanner_log_retention_days' => (int) env('RFID_SCANNER_LOG_RETENTION_DAYS', 30),

    /*
    |--------------------------------------------------------------------------
    | Reader token
    |--------------------------------------------------------------------------
    |
    | The name of the Sanctum token(s) issued by `rfid:issue-token` for the reader server.
    |
    */

    'token_name' => 'rfid-reader-server',

    /*
    |--------------------------------------------------------------------------
    | Health alerts
    |--------------------------------------------------------------------------
    |
    | `rfid:check-health` (every 10 minutes) tells people when the reader server stops sending scans.
    | Keep `enabled` off everywhere except production, so local and test environments never email.
    |
    | emails            Comma separated recipients of the alert email.
    | silence_minutes   The reader server has not contacted EMS (no request with its token) for this long.
    | no_scans_minutes  The reader server is contacting EMS but no known tag has been stored for this long.
    | window_start/end  Alerts are only sent inside this window (app timezone); recoveries are always sent.
    | days              ISO weekdays alerts are sent on (1 = Monday ... 7 = Sunday).
    | reminder_hours    How often the alert is repeated while the problem lasts.
    | token_warning_days  Warn this many days before the last reader token that can expire does.
    |
    */

    'alerts' => [
        'enabled' => (bool) env('RFID_ALERTS_ENABLED', false),
        'emails' => env('RFID_ALERT_EMAILS', 'it.admin@ravago.com.ph'),
        'silence_minutes' => (int) env('RFID_SILENCE_MINUTES', 60),
        'no_scans_minutes' => (int) env('RFID_NO_SCANS_MINUTES', 240),
        'window_start' => env('RFID_ALERT_WINDOW_START', '06:00'),
        'window_end' => env('RFID_ALERT_WINDOW_END', '20:00'),
        'days' => env('RFID_ALERT_DAYS', '1,2,3,4,5,6'),
        'reminder_hours' => (int) env('RFID_REMINDER_HOURS', 6),
        'token_warning_days' => (int) env('RFID_TOKEN_WARNING_DAYS', 30),
        'scanner_inactive_minutes' => (int) env('RFID_SCANNER_INACTIVE_ALERT_MINUTES', 5),
    ],

];
