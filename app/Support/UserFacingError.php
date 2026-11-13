<?php

namespace App\Support;

use Illuminate\Validation\ValidationException;
use Throwable;

/**
 * What a person sees when an action fails. The technical detail (SQL, table names, paths) goes to the log, never to
 * the screen; only validation messages, which are written for people, are shown as they are.
 */
class UserFacingError
{
    public const GENERIC = 'Something went wrong and no changes were saved. Please try again; if it keeps happening, contact IT.';

    public static function from(Throwable $exception): string
    {
        if ($exception instanceof ValidationException) {
            return collect($exception->errors())->flatten()->first() ?? self::GENERIC;
        }

        report($exception);

        return self::GENERIC;
    }
}
