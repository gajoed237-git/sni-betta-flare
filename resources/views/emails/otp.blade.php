<!DOCTYPE html>
<html>

<head>
    <title>Kode OTP Login SIKNUSA FLARE ID</title>
</head>

<body style="font-family: Arial, sans-serif; line-height: 1.6; color: #333;">
    <div style="max-width: 600px; margin: 0 auto; padding: 20px; border: 1px solid #ddd; border-radius: 10px;">
        <div style="text-align: center; margin-bottom: 20px;">
            <img src="{{ $message->embed(public_path('assets/logo_mail.png')) }}" alt="SIKNUSA FLARE ID" style="width: 150px; height: auto;">
        </div>
        <h2 style="color: #3b82f6; text-align: center;">SIKNUSA Mobile Apps - Kode OTP Login Verifikasi Keamanan</h2>
        <p>Halo {{ $user->name }},</p>
        <p>Anda menerima email ini untuk memverifikasi tindakan keamanan pada akun SIKNUSA Mobile Apps Anda.</p>
        <div style="background-color: #f3f4f6; padding: 15px; text-align: center; margin: 20px 0; border-radius: 8px;">
            <h1 style="letter-spacing: 5px; color: #1e293b; margin: 0;">{{ $otp }}</h1>
        </div>
        <p>Gunakan kode di atas untuk melanjutkan proses login. Kode ini berlaku selama 10 menit.</p>
        <p>Jika Anda tidak merasa melakukan permintaan ini, silakan abaikan email ini.</p>
        <hr style="border: 0; border-top: 1px solid #eee; margin: 20px 0;">
        <p style="font-size: 0.8em; color: #777; text-align: center;">&copy; {{ date('Y') }} SIKNUSA FLARE ID. All rights reserved.</p>
    </div>
</body>

</html>