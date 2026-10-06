<?php

namespace App\Filament\Support;

use Illuminate\Support\HtmlString;

/**
 * A widget heading that links to a page.
 *
 * Filament renders it through toHtml(), so the dashboard shows the link. Anything that
 * reads the heading as text (e.g. the Filament Shield role form, which casts it to a
 * string) gets the plain label instead of the raw <a> markup.
 */
class LinkedHeading extends HtmlString
{
    public function __construct(private string $label, string $url)
    {
        parent::__construct(
            '<a href="'.e($url).'" class="hover:underline transition">'.e($label).'</a>'
        );
    }

    public function __toString(): string
    {
        return $this->label;
    }
}
