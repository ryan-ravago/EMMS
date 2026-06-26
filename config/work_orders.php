<?php

return [
    'gmail_account_email' => env('WORK_ORDER_GMAIL_ACCOUNT'),
    'reply_to_address' => env('WORK_ORDER_REPLY_TO_ADDRESS'),
    'reply_marker' => env('WORK_ORDER_REPLY_MARKER', '--- Please reply above this line ---'),
    'google_scopes' => [
        'openid',
        'profile',
        'email',
        'https://www.googleapis.com/auth/gmail.readonly',
        'https://www.googleapis.com/auth/gmail.modify',
    ],
];
