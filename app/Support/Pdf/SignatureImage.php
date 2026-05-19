<?php

namespace App\Support\Pdf;

class SignatureImage
{
    public static function forPdf(?string $source): ?string
    {
        if (! $source || ! function_exists('imagecreatefromstring')) {
            return $source;
        }

        if (is_file($source)) {
            $binary = @file_get_contents($source);

            return is_string($binary) ? self::cropBinary($binary, $source) : $source;
        }

        if (! preg_match('/^data:image\/(png|jpeg|jpg);base64,(.+)$/', $source, $matches)) {
            return $source;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false) {
            return $source;
        }

        return self::cropBinary($binary, $source);
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

        $padding = 18;
        $x = max($bounds['minX'] - $padding, 0);
        $y = max($bounds['minY'] - $padding, 0);
        $cropWidth = min($bounds['maxX'] + $padding, $width - 1) - $x + 1;
        $cropHeight = min($bounds['maxY'] + $padding, $height - 1) - $y + 1;

        $cropped = imagecrop($image, [
            'x' => $x,
            'y' => $y,
            'width' => max($cropWidth, 1),
            'height' => max($cropHeight, 1),
        ]);
        imagedestroy($image);

        if (! $cropped) {
            return $fallback;
        }

        self::makeWhiteTransparent($cropped);

        ob_start();
        imagepng($cropped);
        $png = ob_get_clean();
        imagedestroy($cropped);

        return $png ? 'data:image/png;base64,'.base64_encode($png) : $fallback;
    }

    private static function makeWhiteTransparent($image): void
    {
        imagepalettetotruecolor($image);
        imagealphablending($image, false);
        imagesavealpha($image, true);

        $transparent = imagecolorallocatealpha($image, 255, 255, 255, 127);
        $width = imagesx($image);
        $height = imagesy($image);

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                $color = imagecolorat($image, $x, $y);
                $red = ($color >> 16) & 0xFF;
                $green = ($color >> 8) & 0xFF;
                $blue = $color & 0xFF;

                if ($red > 246 && $green > 246 && $blue > 246) {
                    imagesetpixel($image, $x, $y, $transparent);
                }
            }
        }
    }

    private static function signatureBounds($image, int $width, int $height): ?array
    {
        $bounds = null;

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if (! self::isInkPixel($image, $x, $y)) {
                    continue;
                }

                $bounds = $bounds
                    ? [
                        'minX' => min($bounds['minX'], $x),
                        'minY' => min($bounds['minY'], $y),
                        'maxX' => max($bounds['maxX'], $x),
                        'maxY' => max($bounds['maxY'], $y),
                    ]
                    : ['minX' => $x, 'minY' => $y, 'maxX' => $x, 'maxY' => $y];
            }
        }

        return $bounds;
    }

    private static function isInkPixel($image, int $x, int $y): bool
    {
        $color = imagecolorat($image, $x, $y);
        $alpha = ($color >> 24) & 0x7F;
        $red = ($color >> 16) & 0xFF;
        $green = ($color >> 8) & 0xFF;
        $blue = $color & 0xFF;

        return $alpha < 120 && ($red < 245 || $green < 245 || $blue < 245);
    }
}
