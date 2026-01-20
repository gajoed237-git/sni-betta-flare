<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockSuspiciousAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ip = $request->ip();
        $blockKey = 'blocked_ip_' . $ip;
        $attemptKey = 'access_attempts_' . $ip;

        // Check if IP is already blocked
        if (\Illuminate\Support\Facades\Cache::has($blockKey)) {
            return response()->json([
                'message' => 'Your access has been blocked due to suspicious activity.',
                'code' => 'IP_BLOCKED'
            ], 403);
        }

        // Honeypot: Block IPs trying to access sensitive keywords
        $path = strtolower($request->path());
        $badKeywords = ['wp-admin', 'administrator', 'password', '.env', 'config'];

        foreach ($badKeywords as $keyword) {
            // Only block if it's EXACTLY the path or the start of the path to avoid false positives
            // But verify it's not the actual REAL admin path
            $realPath = env('FILAMENT_PATH', 'system-access');

            if ($path === $keyword || str_starts_with($path, $keyword . '/')) {
                if ($path !== $realPath && !str_starts_with($path, $realPath . '/')) {
                    \Illuminate\Support\Facades\Cache::put($blockKey, true, now()->addDays(7));
                    \Illuminate\Support\Facades\Log::alert("Instant block: IP $ip tried to access honeypot path: $path");

                    \App\Models\SecurityLog::create([
                        'type' => 'security',
                        'event' => 'Honeypot Triggered',
                        'details' => "Attempted access to sensitive path: $path",
                        'ip_address' => $ip,
                        'user_agent' => $request->userAgent(),
                        'metadata' => ['path' => $path]
                    ]);

                    return response()->json(['message' => 'Security block triggered.'], 403);
                }
            }
        }

        // Monitor attempts on sensitive paths (login, register, or high frequency)
        // For simplicity, we count all requests and block if they exceed a threshold
        // You can adjust threshold (e.g., 60 requests per minute per IP)
        $attempts = \Illuminate\Support\Facades\Cache::get($attemptKey, 0);
        $attempts++;
        \Illuminate\Support\Facades\Cache::put($attemptKey, $attempts, now()->addMinutes(1));

        if ($attempts > 500) { // Increased threshold to 500 requests per minute
            \Illuminate\Support\Facades\Cache::put($blockKey, true, now()->addDay());
            \Illuminate\Support\Facades\Log::warning("IP $ip has been blocked for suspicious access frequency.");

            \App\Models\SecurityLog::create([
                'type' => 'security',
                'event' => 'Rate Limit Exceeded',
                'details' => "IP exceeded request threshold (100 req/min)",
                'ip_address' => $ip,
                'user_agent' => $request->userAgent(),
                'metadata' => ['attempts' => $attempts]
            ]);

            return response()->json([
                'message' => 'Access denied. Suspicious activity detected.',
                'code' => 'SUSPICIOUS_ACTIVITY'
            ], 403);
        }

        return $next($request);
    }
}
