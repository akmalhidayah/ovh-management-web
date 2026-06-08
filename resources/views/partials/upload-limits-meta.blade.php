@php
    $iniBytes = static function (mixed $value): int {
        $value = trim((string) $value);

        if ($value === '') {
            return 0;
        }

        $unit = strtolower(substr($value, -1));
        $number = (float) $value;

        return (int) match ($unit) {
            'g' => $number * 1024 * 1024 * 1024,
            'm' => $number * 1024 * 1024,
            'k' => $number * 1024,
            default => $number,
        };
    };

    $uploadMaxBytes = $iniBytes(ini_get('upload_max_filesize'));
    $postMaxBytes = $iniBytes(ini_get('post_max_size'));
    $safePostMaxBytes = $postMaxBytes > 0 ? (int) floor($postMaxBytes * 0.9) : 0;
@endphp
<meta name="upload-max-file-bytes" content="{{ $uploadMaxBytes }}">
<meta name="upload-max-request-bytes" content="{{ $safePostMaxBytes }}">
