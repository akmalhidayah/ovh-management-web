<?php

namespace App\Support\Pdf;

class SignatureImage
{
    public static function forPdf(?string $source): ?string
    {
        if (! $source || ! function_exists('imagecreatefromstring')) {
            return $source;
        }

        if (! preg_match('/^data:image\/(png|jpeg|jpg);base64,(.+)$/', $source, $matches)) {
            return $source;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false) {
            return $source;
        }

        $image = @imagecreatefromstring($binary);
        if (! $image) {
            return $source;
        }

        $width = imagesx($image);
        $height = imagesy($image);
        $bounds = self::signatureBounds($image, $width, $height);

        if (! $bounds) {
            imagedestroy($image);

            return $source;
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
            return $source;
        }

        self::embolden($cropped);

        ob_start();
        imagepng($cropped);
        $png = ob_get_clean();
        imagedestroy($cropped);

        return $png ? 'data:image/png;base64,'.base64_encode($png) : $source;
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

    private static function embolden($image): void
    {
        $width = imagesx($image);
        $height = imagesy($image);
        $inkPixels = [];

        for ($y = 0; $y < $height; $y++) {
            for ($x = 0; $x < $width; $x++) {
                if (self::isInkPixel($image, $x, $y)) {
                    $inkPixels[] = [$x, $y];
                }
            }
        }

        $black = imagecolorallocate($image, 0, 0, 0);

        foreach ($inkPixels as [$x, $y]) {
            for ($offsetY = -1; $offsetY <= 1; $offsetY++) {
                for ($offsetX = -1; $offsetX <= 1; $offsetX++) {
                    $nextX = $x + $offsetX;
                    $nextY = $y + $offsetY;

                    if ($nextX >= 0 && $nextY >= 0 && $nextX < $width && $nextY < $height) {
                        imagesetpixel($image, $nextX, $nextY, $black);
                    }
                }
            }
        }
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
