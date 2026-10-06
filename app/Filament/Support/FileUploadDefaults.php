<?php

namespace App\Filament\Support;

use App\Support\Media\ImageOptimizer;
use Closure;
use Filament\Forms\Components\FileUpload;
use Livewire\Features\SupportFileUploads\TemporaryUploadedFile;

/**
 * Defaults for every FileUpload in the app: videos are refused, and images are optimized
 * before they are stored, so the path saved to the database already points at the smaller file.
 *
 * Registered once in AppServiceProvider via FileUpload::configureUsing(); a field can still
 * override the save step with its own ->saveUploadedFileUsing().
 */
class FileUploadDefaults
{
    public static function configure(FileUpload $upload): FileUpload
    {
        // Wrap Filament's own save logic (disk, directory, naming, visibility) instead of copying it.
        $save = (fn () => $this->saveUploadedFileUsing)->call($upload);

        return $upload
            ->rule(static fn (): Closure => static function (string $attribute, mixed $value, Closure $fail): void {
                if ($value instanceof TemporaryUploadedFile && str_starts_with($value->getMimeType(), 'video/')) {
                    $fail('Videos are not accepted.');
                }
            })
            ->saveUploadedFileUsing(function (FileUpload $component, TemporaryUploadedFile $file) use ($save): ?string {
                rescue(fn () => app(ImageOptimizer::class)->optimize($file->getRealPath()), report: false);

                return $component->evaluate($save, ['file' => $file]);
            });
    }
}
