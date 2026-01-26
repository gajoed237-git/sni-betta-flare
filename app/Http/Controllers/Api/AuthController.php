<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use App\Mail\UserLoginOtp;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Str;

class AuthController extends Controller
{
    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email:rfc,dns|max:255|unique:users',
            'password' => 'required|string|min:8|confirmed',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'device_name' => 'nullable|string',
        ]);

        $email = strtolower(trim($request->email));

        $user = User::create([
            'name' => $request->name,
            'email' => $email,
            'password' => Hash::make($request->password),
            'phone' => $request->phone,
            'address' => $request->address,
            'role' => 'participant',
            'is_active' => true,
            'email_verified_at' => now(), // Force auto-verify for production
        ]);

        $token = $user->createToken($request->device_name ?? 'mobile')->plainTextToken;

        return response()->json([
            'status' => 'success',
            'message' => 'Registrasi berhasil.',
            'user' => $user,
            'token' => $token,
        ], 201);
    }


    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email:rfc,dns|max:255',
            'password' => 'required|string',
        ]);

        $email = strtolower(trim($request->email));

        $user = User::where('email', $email)->first();

        if (! $user || ! Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['Email atau password salah.'],
            ]);
        }

        if (! $user->is_active) {
            \App\Models\SecurityLog::create([
                'type' => 'security',
                'event' => 'Blocked User Login Attempt',
                'details' => "Blocked user " . $user->email . " attempted to login.",
                'ip_address' => $request->ip(),
                'user_agent' => $request->userAgent(),
                'user_id' => $user->id
            ]);

            throw ValidationException::withMessages([
                'email' => ['Akun Anda diblokir. Silakan hubungi admin.'],
            ]);
        }

        // Auto-verify email for participant on first login if not yet verified
        if ($user->role === 'participant' && ! $user->email_verified_at) {
            $user->update([
                'email_verified_at' => now(),
            ]);
        }

        $token = $user->createToken($request->device_name ?? 'mobile')->plainTextToken;

        return response()->json([
            'token' => $token,
            'user' => $user,
        ]);
    }

    public function requestOtpSettings(Request $request)
    {
        $user = $request->user();

        // Generate OTP
        $otp = sprintf("%06d", mt_rand(1, 999999));
        $user->update([
            'login_otp' => $otp,
            'otp_expires_at' => now()->addMinutes(10),
        ]);

        // Send OTP via Email
        try {
            Mail::to($user->email)->send(new UserLoginOtp($otp, $user));
        } catch (\Exception $e) {
            \Illuminate\Support\Facades\Log::error("Failed to send settings OTP: " . $e->getMessage());
            return response()->json(['message' => 'Gagal mengirim email verifikasi.'], 500);
        }

        return response()->json([
            'message' => 'Kode verifikasi telah dikirim ke email Anda.',
        ]);
    }

    public function updateSettings(Request $request)
    {
        $request->validate([
            'otp_enabled' => 'required|boolean',
            'otp' => 'required_if:otp_enabled,true|string|size:6',
        ]);

        $user = $request->user();

        // If enabling OTP, verify the code first
        if ($request->otp_enabled) {
            if ($user->login_otp !== $request->otp || now()->gt($user->otp_expires_at)) {
                throw ValidationException::withMessages([
                    'otp' => ['Kode verifikasi tidak valid atau sudah kadaluwarsa.'],
                ]);
            }

            // Clear OTP after success
            $user->login_otp = null;
            $user->otp_expires_at = null;
        }

        $user->otp_enabled = $request->otp_enabled;
        $user->save();

        return response()->json([
            'message' => 'Pengaturan berhasil diperbarui.',
            'user' => $user,
        ]);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(['message' => 'Logged out']);
    }

    public function me(Request $request)
    {
        return response()->json($request->user());
    }

    public function updateProfile(Request $request)
    {
        $user = $request->user();

        $request->validate([
            'name' => 'required|string|max:255',
            'phone' => 'nullable|string|max:20',
            'address' => 'nullable|string',
            'photo' => 'nullable|image|max:2048', // Max 2MB
        ]);

        $data = [
            'name' => $request->name,
            'phone' => $request->phone,
            'address' => $request->address,
        ];

        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($user->profile_photo_path && \Illuminate\Support\Facades\Storage::disk('public')->exists($user->profile_photo_path)) {
                \Illuminate\Support\Facades\Storage::disk('public')->delete($user->profile_photo_path);
            }

            $path = $request->file('photo')->store('profile-photos', 'public');
            $data['profile_photo_path'] = $path;
        }

        $user->update($data);

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user,
        ]);
    }
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|string|min:8|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            throw ValidationException::withMessages([
                'current_password' => ['Password saat ini tidak cocok.'],
            ]);
        }

        $user->update([
            'password' => Hash::make($request->new_password),
        ]);

        return response()->json([
            'message' => 'Password berhasil diubah.',
        ]);
    }
}
