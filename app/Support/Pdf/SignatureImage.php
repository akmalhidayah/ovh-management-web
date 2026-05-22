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
            return $path;
        }

        if (! preg_match('/^data:image\/(png|jpeg|jpg);base64,(.+)$/', $source, $matches)) {
            return $source;
        }

        $binary = base64_decode($matches[2], true);
        if ($binary === false || @getimagesizefromstring($binary) === false) {
            return null;
        }

        $extension = $matches[1] === 'png' ? 'png' : 'jpg';
        $relativePath = 'signatures/pdf-cache/'.sha1($binary).'.'.$extension;

        if (! Storage::disk('public')->exists($relativePath)) {
            Storage::disk('public')->put($relativePath, $binary);
        }

        return Storage::disk('public')->path($relativePath);
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

}
