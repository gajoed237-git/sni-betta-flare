<!DOCTYPE html>
<html>

<head>
    <meta charset="utf-8">
</head>

<body style="font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; line-height: 1.6; color: #333; margin: 0; padding: 0; background-color: #f1f5f9;">
    <table width="100%" border="0" cellspacing="0" cellpadding="0" style="background-color: #f1f5f9; padding: 20px;">
        <tr>
            <td align="center">
                <table width="600" border="0" cellspacing="0" cellpadding="0" style="background-color: #ffffff; border-radius: 12px; overflow: hidden; border: 1px solid #e2e8f0;">
                    <!-- HEADER -->
                    <tr>
                        <td align="center" style="padding: 40px 20px 20px 20px;">
                            <img src="{{ $message->embed(public_path('assets/logo_mail.png')) }}" alt="SIKNUSA" width="80" height="80" style="display: block; margin-bottom: 20px;">
                            <h1 style="margin: 0; color: #1e293b; font-size: 24px;">Verifikasi Akun</h1>
                        </td>
                    </tr>

                    <!-- CONTENT -->
                    <tr>
                        <td style="padding: 0 40px 20px 40px; text-align: left;">
                            <p style="font-size: 16px;">Halo, <strong>{{ $user->name }}</strong>!</p>
                            <p style="font-size: 16px;">Terima kasih telah mendaftar di <strong>SIKNUSA FLARE ID</strong>. Untuk mulai menggunakan aplikasi, silakan verifikasi akun Anda.</p>
                            <p style="font-size: 16px;">Klik tombol di bawah ini untuk mengaktifkan akun Anda:</p>
                        </td>
                    </tr>

                    <!-- BUTTON -->
                    <tr>
                        <td align="center" style="padding: 20px 40px 40px 40px;">
                            <a href="{{ route('verify.bridge', ['token' => $token, 'email' => $user->email]) }}"
                                style="display: inline-block; padding: 16px 32px; background-color: #2563eb; color: #ffffff; text-decoration: none; border-radius: 10px; font-weight: bold; font-size: 16px; box-shadow: 0 4px 6px -1px rgba(37, 99, 235, 0.2);">
                                Verifikasi Akun Sekarang
                            </a>
                        </td>
                    </tr>

                    <!-- FOOTER -->
                    <tr>
                        <td align="center" style="background-color: #f8fafc; padding: 20px; font-size: 12px; color: #94a3b8;">
                            <p style="margin: 0;">&copy; {{ date('Y') }} SIKNUSA FLARE ID. Semua Hak Dilindungi.</p>
                            <p style="margin: 5px 0 0 0;">Pesan ini dikirim secara otomatis, mohon tidak membalas.</p>
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
    </table>
</body>

</html>