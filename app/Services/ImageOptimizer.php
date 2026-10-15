<?php

namespace App\Services;

use GdImage;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

/**
 * Validates, resizes and re-encodes admin-uploaded images on the public disk.
 *
 * Re-encoding through GD is also a security measure: the stored file is always
 * a freshly generated image, never the uploaded bytes (so embedded payloads are dropped).
 * For every image it writes the main file (max 1600px wide) and a 640px variant
 * named "<name>-640.<ext>", which the frontend uses for srcset.
 */
class ImageOptimizer
{
    private const MAX_PIXELS = 40_000_000;
    private const MIN_WIDTH = 300;
    private const MIN_HEIGHT = 200;

    /** @return string path relative to the public disk, e.g. "gallery/1b2c...webp" */
    public function store(UploadedFile $file, string $directory, string $field = 'image', int $maxWidth = 1600): string
    {
        $disk = Storage::disk('public');
        $directory = trim($directory, '/');
        $name = (string) Str::uuid();

        $size = @getimagesize($file->getRealPath());
        if ($size === false || $size[0] * $size[1] > self::MAX_PIXELS) {
            throw ValidationException::withMessages([$field => 'The uploaded file is not a valid image (or is too large in pixels).']);
        }

        if ($size[0] < self::MIN_WIDTH || $size[1] < self::MIN_HEIGHT) {
            throw ValidationException::withMessages([$field => 'The image is too small. Please upload one at least '.self::MIN_WIDTH.' x '.self::MIN_HEIGHT.' pixels (1600 px wide is best).']);
        }

        // Without GD we cannot optimise; fall back to storing the (already MIME-validated) file.
        if (! extension_loaded('gd')) {
            $extension = $file->guessExtension() ?: 'jpg';
            $disk->putFileAs($directory, $file, "{$name}.{$extension}");

            return "{$directory}/{$name}.{$extension}";
        }

        $image = @imagecreatefromstring((string) file_get_contents($file->getRealPath()));
        if ($image === false) {
            throw ValidationException::withMessages([$field => 'The uploaded file is not a valid image.']);
        }

        $image = $this->orient($image, $file->getRealPath());
        $extension = function_exists('imagewebp') ? 'webp' : 'jpg';

        $disk->put("{$directory}/{$name}.{$extension}", $this->encode($image, $maxWidth, $extension));
        $disk->put("{$directory}/{$name}-640.{$extension}", $this->encode($image, 640, $extension));

        return "{$directory}/{$name}.{$extension}";
    }

    /** Removes an uploaded image and its small variant. Site-relative defaults ("/images/...") are never touched. */
    public function delete(?string $path): void
    {
        if ($path === null || $path === '' || str_starts_with($path, '/') || str_starts_with($path, 'http') || str_contains($path, '..')) {
            return;
        }

        $disk = Storage::disk('public');
        $disk->delete($path);
        $disk->delete((string) preg_replace('/(\.\w+)$/', '-640$1', $path));
    }

    private function encode(GdImage $image, int $maxWidth, string $extension): string
    {
        if (imagesx($image) > $maxWidth) {
            $image = imagescale($image, $maxWidth) ?: $image;
        }

        imagesavealpha($image, true);

        ob_start();
        $extension === 'webp' ? imagewebp($image, null, 82) : imagejpeg($image, null, 85);

        return (string) ob_get_clean();
    }

    /** Phones store rotation in EXIF; re-encoding drops it, so apply it first. */
    private function orient(GdImage $image, string $path): GdImage
    {
        if (! function_exists('exif_read_data')) {
            return $image;
        }

        $exif = @exif_read_data($path);
        $orientation = is_array($exif) ? (int) ($exif['Orientation'] ?? 1) : 1;
        $angle = match ($orientation) {
            3 => 180,
            6 => -90,
            8 => 90,
            default => 0,
        };

        return $angle !== 0 ? (imagerotate($image, $angle, 0) ?: $image) : $image;
    }
}
