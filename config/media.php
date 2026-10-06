<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Media Optimization
    |--------------------------------------------------------------------------
    |
    | Images are shrunk (PHP's GD extension) before they are stored, for form
    | uploads and the Gmail work order sync, so the path saved to the database
    | already points at the optimized file. Videos are not accepted at all.
    | Set MEDIA_OPTIMIZE_ENABLED=false to switch the optimization off.
    |
    */

    'enabled' => env('MEDIA_OPTIMIZE_ENABLED', true),

    'image' => [
        // Longest side in pixels. Bigger images are scaled down, never up.
        'max_dimension' => (int) env('MEDIA_IMAGE_MAX_DIMENSION', 1920),

        // JPEG / WebP quality (1-100). PNGs are always re-compressed losslessly.
        'quality' => (int) env('MEDIA_IMAGE_QUALITY', 82),

        // Images under this size (and within max_dimension) are left alone.
        'min_bytes' => 200 * 1024,
    ],

];
