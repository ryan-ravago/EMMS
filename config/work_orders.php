<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Work order reply mailbox
    |--------------------------------------------------------------------------
    |
    | Work order emails ask people to reply; `work-orders:sync-google-replies` (every minute) reads those replies
    | from a Gmail inbox and adds them to the work order as updates.
    |
    | gmail_account_email  The EMS user whose Gmail inbox is read. It must be an active user that has signed in to
    |                      EMS with Google at least once (that is what stores the Gmail permission). When empty,
    |                      nothing is synced.
    | reply_to_address     The address replies are sent to (the work order number is added as "+WO-..."). Normally
    |                      the same mailbox as gmail_account_email.
    | reply_marker         The line in the email that separates the reply from the quoted message.
    | reply_sync_enabled   Master switch for the sync.
    | reply_max_age_days   Unread replies older than this many days are not read.
    | reply_batch_size     How many replies are read per run.
    |
    */

    'gmail_account_email' => env('WORK_ORDER_GMAIL_ACCOUNT'),
    'reply_to_address' => env('WORK_ORDER_REPLY_TO_ADDRESS'),
    'reply_marker' => env('WORK_ORDER_REPLY_MARKER', '--- Please reply above this line ---'),
    'reply_sync_enabled' => (bool) env('WORK_ORDER_REPLY_SYNC_ENABLED', true),
    'reply_max_age_days' => (int) env('WORK_ORDER_REPLY_MAX_AGE_DAYS', 7),
    'reply_batch_size' => (int) env('WORK_ORDER_REPLY_BATCH_SIZE', 50),

    'google_scopes' => [
        'openid',
        'profile',
        'email',
        'https://www.googleapis.com/auth/gmail.readonly',
        'https://www.googleapis.com/auth/gmail.modify',
    ],
];
