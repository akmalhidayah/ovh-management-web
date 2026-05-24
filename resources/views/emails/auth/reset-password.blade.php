@php
    $safeName = trim((string) ($userName ?? 'Pengguna'));
    $logoSource = $logoUrl ?? '';

    if (isset($message) && ! empty($logoPath) && file_exists($logoPath)) {
        $logoSource = $message->embedData(
            file_get_contents($logoPath),
            'semen-tonasa-logo.png',
            'image/png'
        );
    }
@endphp
<!doctype html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Reset Password Akun Overhaul</title>
</head>
<body style="margin:0;padding:0;background:#eef3f8;font-family:Arial,Helvetica,sans-serif;color:#172033;">
    <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%;background:#eef3f8;margin:0;padding:0;">
        <tr>
            <td align="center" style="padding:34px 16px;">
                <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="width:100%;max-width:640px;border-collapse:separate;border-spacing:0;">
                    <tr>
                        <td style="border-radius:22px;overflow:hidden;background:#ffffff;box-shadow:0 20px 48px rgba(15,23,42,.16);">
                            <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                <tr>
                                    <td style="background:#8b1e2d;padding:26px 32px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0">
                                            <tr>
                                                <td style="vertical-align:middle;">
                                                    <table role="presentation" cellspacing="0" cellpadding="0">
                                                        <tr>
                                                            <td style="width:72px;height:72px;border-radius:18px;background:#ffffff;text-align:center;vertical-align:middle;box-shadow:0 10px 26px rgba(15,23,42,.18);">
                                                                <img src="{{ $logoSource }}" width="58" alt="Semen Tonasa" style="width:58px;max-width:58px;height:auto;display:inline-block;border:0;vertical-align:middle;">
                                                            </td>
                                                            <td style="padding-left:16px;color:#ffffff;">
                                                                <div style="font-size:13px;line-height:18px;font-weight:700;letter-spacing:.08em;text-transform:uppercase;opacity:.82;">Unit Overhaul</div>
                                                                <div style="font-size:24px;line-height:30px;font-weight:800;margin-top:3px;">PT. Semen Tonasa</div>
                                                            </td>
                                                        </tr>
                                                    </table>
                                                </td>
                                                <td align="right" style="vertical-align:middle;">
                                                    <span style="display:inline-block;border:1px solid rgba(255,255,255,.35);border-radius:999px;padding:8px 12px;color:#ffffff;font-size:12px;font-weight:700;">Reset Password</span>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:36px 32px 12px;">
                                        <h1 style="margin:0;color:#172033;font-size:28px;line-height:36px;font-weight:800;">Halo {{ $safeName }}</h1>
                                        <p style="margin:16px 0 0;color:#475569;font-size:16px;line-height:26px;">
                                            Kami menerima permintaan reset password untuk akun Overhaul PT. Semen Tonasa Anda. Gunakan tombol di bawah untuk membuat password baru.
                                        </p>
                                    </td>
                                </tr>
                                <tr>
                                    <td align="center" style="padding:24px 32px 26px;">
                                        <a href="{{ $resetUrl }}" style="display:inline-block;background:#8b1e2d;color:#ffffff;text-decoration:none;border-radius:12px;padding:15px 28px;font-size:15px;line-height:20px;font-weight:800;box-shadow:0 12px 26px rgba(139,30,45,.28);">Reset Password</a>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 32px 28px;">
                                        <table role="presentation" width="100%" cellspacing="0" cellpadding="0" style="background:#f8fafc;border:1px solid #e2e8f0;border-radius:14px;">
                                            <tr>
                                                <td style="padding:18px 20px;">
                                                    <div style="color:#0f172a;font-size:14px;font-weight:800;margin-bottom:6px;">Link berlaku {{ $expiresIn }} menit</div>
                                                    <div style="color:#64748b;font-size:13px;line-height:22px;">Jika Anda tidak meminta reset password, abaikan email ini. Password lama tetap aktif sampai Anda membuat password baru.</div>
                                                </td>
                                            </tr>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="padding:0 32px 36px;">
                                        <div style="color:#64748b;font-size:12px;line-height:20px;margin-bottom:8px;">Jika tombol tidak bisa diklik, salin link berikut ke browser:</div>
                                        <div style="word-break:break-all;color:#2563eb;font-size:12px;line-height:19px;">{{ $resetUrl }}</div>
                                    </td>
                                </tr>
                                <tr>
                                    <td style="background:#f8fafc;border-top:1px solid #e2e8f0;padding:18px 32px;color:#64748b;font-size:12px;line-height:19px;text-align:center;">
                                        Email ini dikirim otomatis oleh sistem Overhaul PT. Semen Tonasa.
                                    </td>
                                </tr>
                            </table>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>
</html>
