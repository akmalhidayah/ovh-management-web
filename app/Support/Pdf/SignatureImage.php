<?php

namespace App\Support\Pdf;

use Illuminate\Support\Facades\Storage;

class SignatureImage
{
    public static function forPdf(?string $source): ?string
    {
        if (! $source) {
            return $source;
        }

        if ($path = self::localPathForSource($source)) {
            $binary = @file_get_contents($path);

            if (! is_string($binary)) {
                return $source;
            }

            $fallback = self::dataUri($binary, self::mimeType($path));

            if (! function_exists('imagecreatefromstring')) {
                return $fallback;
            }

            return self::cropBinary($binary, $fallback);
        }

        if (! preg_match('/^data:image\/(png|jpeg|jpg);base64,(.+)$/', $source, $matches)) {
            return $source;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false) {
            return $source;
        }

        if (! function_exists('imagecreatefromstring')) {
            return $source;
        }

        return self::cropBinary($binary, $source);
    }

    private static function localPathForSource(string $source): ?string
    {
        if (is_file($source)) {
            return $source;
        }

        $path = parse_url($source, PHP_URL_PATH) ?: $source;

        if (str_starts_with($path, '/storage/')) {
            $relative = urldecode(substr($path, strlen('/storage/')));

            if (Storage::disk('public')->exists($relative)) {
                return Storage::disk('public')->path($relative);
            }

            $publicStoragePath = public_path('storage/'.$relative);

            return is_file($publicStoragePath) ? $publicStoragePath : null;
        }

        $relative = ltrim($source, '/');

        if (Storage::disk('public')->exists($relative)) {
            return Storage::disk('public')->path($relative);
        }

        $publicPath = public_path($relative);

        return is_file($publicPath) ? $publicPath : null;
    }

    private static function dataUri(string $binary, string $mime): string
    {
        return 'data:'.$mime.';base64,'.base64_encode($binary);
    }

    private static function mimeType(string $path): string
    {
        $extension = strtolower(pathinfo($path, PATHINFO_EXTENSION));

        return match ($extension) {
            'jpg', 'jpeg' => 'image/jpeg',
            'gif' => 'image/gif',
            'webp' => 'image/webp',
            default => 'image/png',
        };
    }

    private static function cropBinary(string $binary, string $fallback): string
    {
        $image = @imagecreatefromstring($binary);

        if (! $image) {
            return $fallback;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $bounds = self::signatureBounds($image, $width, $height);

        if (! $bounds) {
            imagedestroy($image);

            return $fallback;
        }

        $padding = max(2, (int) round(max($width, $height) * 0.015));
        $minX = max(0, $bounds['minX'] - $padding);
        $minY = max(0, $bounds['minY'] - $padding);
        $maxX = min($width - 1, $bounds['maxX'] + $padding);
        $maxY = min($height - 1, $bounds['maxY'] + $padding);
        $cropWidth = $maxX - $minX + 1;
        $cropHeight = $maxY - $minY + 1;

        $crop = imagecreatetruecolor($cropWidth, $cropHeight);
        imagesavealpha($crop, true);
        $transparent = imagecolorallocatealpha($crop, 255, 255, 255, 127);
        imagefill($crop, 0, 0, $transparent);
        imagecopy($crop, $image, 0, 0, $minX, $minY, $cropWidth, $cropHeight);

        self::makeWhiteTransparent($crop);

        ob_start();
        imagepng($crop);
        $png = ob_get_clean();

        imagedestroy($crop);
        imagedestroy($image);

        return is_string($png) && $png !== ''
            ? self::dataUri($png, 'image/png')
            : $fallback;
    }

    private static function makeWhiteTransparent($image): void
    {
        $width = imagesx($image);
        $height = imagesy($image);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorat($image, $x, $y);
                $rgba = imagecolorsforindex($image, $color);

                if (($rgba['red'] ?? 255) > 245 && ($rgba['green'] ?? 255) > 245 && ($rgba['blue'] ?? 255) > 245) {
                    imagesetpixel($image, $x, $y, imagecolorallocatealpha($image, 255, 255, 255, 127));
                }
            }
        }
    }

    private static function signatureBounds($image, int $width, int $height): ?array
    {
        $minX = $width;
        $minY = $height;
        $maxX = -1;
        $maxY = -1;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorat($image, $x, $y);
                $rgba = imagecolorsforindex($image, $color);

                if (! self::isInkPixel($rgba)) {
                    continue;
                }

                $minX = min($minX, $x);
                $minY = min($minY, $y);
                $maxX = max($maxX, $x);
                $maxY = max($maxY, $y);
            }
        }

        if ($maxX < $minX || $maxY < $minY) {
            return null;
        }

        return compact('minX', 'minY', 'maxX', 'maxY');
    }

    private static function isInkPixel(array $rgba): bool
    {
        $alpha = $rgba['alpha'] ?? 0;

        if ($alpha >= 120) {
            return false;
        }

        return ($rgba['red'] ?? 255) < 235
            || ($rgba['green'] ?? 255) < 235
            || ($rgba['blue'] ?? 255) < 235;
    }
}
