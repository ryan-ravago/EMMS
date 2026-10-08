<?php

namespace App\Filament\Support;

use App\Support\Media\ImageOptimizer;
use Closure;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Defaults for every FileUpload in the app: only common documents and images are accepted,
 * videos and SVGs (which can carry scripts) are refused, and images are optimized before they
 * are stored, so the path saved to the database already points at the smaller file.
 *
 * Registered once in AppServiceProvider via FileUpload::configureUsing(); a field can still
 * narrow the types with ->image() / ->acceptedFileTypes(), or override the save step with its
 * own ->saveUploadedFileUsing().
 */
class FileUploadDefaults
{
    /**
     * @var list<string>
     */
    public const ACCEPTED_FILE_TYPES = [
        'image/*',
        'application/pdf',
        'application/msword',
        'application/vnd.openxmlformats-officedocument.wordprocessingml.document',
        'application/vnd.ms-excel',
        'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet',
        'text/csv',
    ];

    public static function configure(FileUpload $upload): FileUpload
    {
        // Wrap Filament's own save logic (disk, directory, naming, visibility) instead of copying it.
        $save = (fn () => $this->saveUploadedFileUsing)->call($upload);

        return $upload
            ->acceptedFileTypes(self::ACCEPTED_FILE_TYPES)
            ->rule(static fn (): Closure => static function (string $attribute, mixed $value, Closure $fail): void {
                if (! $value instanceof TemporaryUploadedFile) {
                    return;
                }

                if (str_starts_with($value->getMimeType(), 'video/')) {
                    $fail('Videos are not accepted.');
                }

                if (str_starts_with($value->getMimeType(), 'image/svg')) {
                    $fail('SVG images are not accepted.');
                }
            })
            ->saveUploadedFileUsing(function (FileUpload $component, TemporaryUploadedFile $file) use ($save): ?string {
                rescue(fn () => app(ImageOptimizer::class)->optimize($file->getRealPath()), report: false);

                return $component->evaluate($save, ['file' => $file]);
            });
    }
}
