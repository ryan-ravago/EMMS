<?php

namespace App\Support\Media;

use finfo;
use Illuminate\Support\Facades\Log;
use Throwable;

/**
 * Shrinks images (GD) before they are stored, so the path that ends up in the database
 * already points at the optimized file.
 *
 * It never blocks an upload: anything it can't or shouldn't touch (other file types, small
 * images, a failed run, a result that isn't smaller) is left exactly as it was.
 */
class ImageOptimizer
{
    private const MIMES = ['image/jpeg', 'image/png', 'image/webp'];

    /**
     * Optimize the image at $path in place.
     */
    public function optimize(string $path): void
    {
        if (! config('media.enabled') || ! is_file($path)) {
            return;
        }

        try {
            $mime = (string) mime_content_type($path);

            if (in_array($mime, self::MIMES, true)) {
                $this->shrink($path, $mime);
            }
        } catch (Throwable $e) {
            Log::warning('Image optimization skipped: '.$e->getMessage(), ['file' => basename($path)]);
        }
    }

    /**
     * Same as optimize(), for a file held in memory (e.g. an email attachment).
     */
    public function optimizeContents(string $contents): string
    {
        if (! config('media.enabled') || ! in_array((new finfo(FILEINFO_MIME_TYPE))->buffer($contents), self::MIMES, true)) {
            return $contents;
        }

        $temp = tempnam(sys_get_temp_dir(), 'image');

        try {
            file_put_contents($temp, $contents);
            $this->optimize($temp);

            return (string) file_get_contents($temp);
        } finally {
            @unlink($temp);
        }
    }

    private function shrink(string $path, string $mime): void
    {
        $maxSide = (int) config('media.image.max_dimension');
        [$width, $height] = getimagesize($path) ?: [0, 0];

        $isSmallEnough = $width <= $maxSide && $height <= $maxSide && filesize($path) < (int) config('media.image.min_bytes');

        if ($width < 1 || $isSmallEnough || ! $this->hasMemoryFor($width, $height)) {
            return;
        }

        $angle = 0;

        if ($mime === 'image/jpeg') {
            // Re-encoding drops the EXIF orientation tag, so turn it into real rotation first.
            $orientation = function_exists('exif_read_data') ? (int) (@exif_read_data($path)['Orientation'] ?? 1) : null;
            $angle = match ($orientation) {
                1 => 0,
                3 => 180,
                6 => -90,
                8 => 90,
                default => null,
            };

            if ($angle === null) {
                return; // Mirrored/unknown orientation, or no EXIF support: keep the original.
            }
        }

        $image = match ($mime) {
            'image/jpeg' => imagecreatefromjpeg($path),
            'image/png' => imagecreatefrompng($path),
            'image/webp' => imagecreatefromwebp($path),
        };

        if ($image === false) {
            return;
        }

        imagealphablending($image, false);
        imagesavealpha($image, true);

        $ratio = min(1, $maxSide / max($width, $height));

        if ($ratio < 1) {
            $image = imagescale($image, max(1, (int) round($width * $ratio)), max(1, (int) round($height * $ratio)), IMG_BICUBIC);
        }

        if ($image !== false && $angle !== 0) {
            $image = imagerotate($image, $angle, 0);
        }

        if ($image === false) {
            return;
        }

        $temp = $path.'.optimized';
        $quality = (int) config('media.image.quality');

        try {
            $saved = match ($mime) {
                'image/jpeg' => imageinterlace($image, true) !== 0 && imagejpeg($image, $temp, $quality),
                'image/png' => imagepng($image, $temp, 9),
                'image/webp' => imagewebp($image, $temp, $quality),
            };

            if ($saved && filesize($temp) < filesize($path)) {
                rename($temp, $path);
                clearstatcache(true, $path);
            }
        } finally {
            @unlink($temp);
        }
    }

    private function hasMemoryFor(int $width, int $height): bool
    {
        $limit = ini_parse_quantity((string) ini_get('memory_limit'));

        // A decoded image takes ~4 bytes per pixel in GD; keep headroom for the scaled copy.
        return $limit < 0 || memory_get_usage() + $width * $height * 5 < $limit;
    }
}
