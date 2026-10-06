<?php

namespace Tests\Unit;

use App\Support\Media\ImageOptimizer;
use Tests\TestCase;

class ImageOptimizerTest extends TestCase
{
    private string $directory;

    protected function setUp(): void
    {
        parent::setUp();

        $this->directory = sys_get_temp_dir().'/image-optimizer-'.uniqid();
        mkdir($this->directory);
    }

    protected function tearDown(): void
    {
        array_map('unlink', glob($this->directory.'/*'));
        rmdir($this->directory);

        parent::tearDown();
    }

    public function test_it_downsizes_and_recompresses_a_large_jpeg(): void
    {
        $path = $this->makeJpeg(3200, 2400);
        $originalSize = filesize($path);

        (new ImageOptimizer)->optimize($path);

        $this->assertLessThan($originalSize, filesize($path));
        $this->assertSame([1920, 1440], array_slice(getimagesize($path), 0, 2));
        $this->assertSame('image/jpeg', mime_content_type($path));
    }

    public function test_it_leaves_small_images_alone(): void
    {
        $path = $this->makeJpeg(400, 300);
        $before = file_get_contents($path);

        (new ImageOptimizer)->optimize($path);

        $this->assertSame($before, file_get_contents($path));
    }

    public function test_it_bakes_the_exif_orientation_into_the_pixels(): void
    {
        $path = $this->makeJpeg(3000, 2000, exifOrientation: 6);

        (new ImageOptimizer)->optimize($path);

        // Landscape pixels flagged "rotate 90° clockwise" must come out as a portrait picture.
        $this->assertSame([1280, 1920], array_slice(getimagesize($path), 0, 2));
    }

    public function test_it_keeps_the_transparency_of_a_png(): void
    {
        $image = imagecreatetruecolor(2400, 1600);
        imagealphablending($image, false);
        imagesavealpha($image, true);
        imagefill($image, 0, 0, imagecolorallocatealpha($image, 0, 0, 0, 127));
        $path = $this->directory.'/logo.png';
        imagepng($image, $path, 0);
        $originalSize = filesize($path);

        (new ImageOptimizer)->optimize($path);

        $this->assertLessThan($originalSize, filesize($path));
        $optimized = imagecreatefrompng($path);
        $this->assertSame(127, (imagecolorat($optimized, 10, 10) >> 24) & 0x7F);
    }

    public function test_it_does_nothing_when_disabled(): void
    {
        config(['media.enabled' => false]);
        $path = $this->makeJpeg(3200, 2400);
        $before = file_get_contents($path);

        (new ImageOptimizer)->optimize($path);

        $this->assertSame($before, file_get_contents($path));
    }

    public function test_it_leaves_other_file_types_untouched(): void
    {
        $pdf = '%PDF-1.4 '.str_repeat('x', 500_000);

        $this->assertSame($pdf, (new ImageOptimizer)->optimizeContents($pdf));
    }

    public function test_it_optimizes_image_contents(): void
    {
        $contents = file_get_contents($this->makeJpeg(3200, 2400));

        $this->assertLessThan(strlen($contents), strlen((new ImageOptimizer)->optimizeContents($contents)));
    }

    /**
     * A JPEG full of random shapes, so it is big enough to be worth optimizing.
     */
    private function makeJpeg(int $width, int $height, ?int $exifOrientation = null): string
    {
        $image = imagecreatetruecolor($width, $height);

        for ($i = 0; $i < 150; $i++) {
            imagefilledellipse(
                $image,
                random_int(0, $width),
                random_int(0, $height),
                random_int(50, 600),
                random_int(50, 600),
                imagecolorallocate($image, random_int(0, 255), random_int(0, 255), random_int(0, 255)),
            );
        }

        $path = $this->directory.'/photo.jpg';
        imagejpeg($image, $path, 100);

        if ($exifOrientation !== null) {
            $exif = "Exif\x00\x00MM\x00\x2A\x00\x00\x00\x08\x00\x01\x01\x12\x00\x03\x00\x00\x00\x01\x00".chr($exifOrientation)."\x00\x00\x00\x00\x00\x00";
            $jpeg = file_get_contents($path);
            file_put_contents($path, substr($jpeg, 0, 2)."\xFF\xE1".pack('n', strlen($exif) + 2).$exif.substr($jpeg, 2));
        }

        return $path;
    }
}
