<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Log retention
    |--------------------------------------------------------------------------
    |
    | Log rows older than this many days are deleted once a day by the scheduled
    | "model:prune" run (see routes/console.php). It applies to the activity log
    | (which also holds the inspection sync logs), the maintenance inspection
    | logs and the AIMS inspection logs, and nothing else.
    |
    | A row is deleted only when it is older than the limit: a row exactly this many
    | days old, or newer, is kept.
    |
    */

    'days' => (int) env('LOG_RETENTION_DAYS', 30),

    /*
    | The time of day (server time) the cleanup runs.
    */

    'run_at' => env('LOG_RETENTION_RUN_AT', '03:00'),

    /*
    | Rows deleted per query. Deleting in chunks keeps each statement short, so large
    | tables are cleaned without long locks or high memory use.
    */

    'chunk' => (int) env('LOG_RETENTION_CHUNK', 1000),

];
