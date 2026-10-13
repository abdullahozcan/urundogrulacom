<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Baseline security response headers.
 *
 * There was no CSP, HSTS or framing protection anywhere in the app, which
 * left the raw-output settings (`custom_head_scripts`, `hero_title`) and the
 * Leaflet popup HTML with no compensating control.
 */
class SecurityHeaders
{
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        $headers = [
            'X-Content-Type-Options' => 'nosniff',
            'X-Frame-Options' => 'SAMEORIGIN',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'X-Permitted-Cross-Domain-Policies' => 'none',
            'Cross-Origin-Opener-Policy' => 'same-origin',
            // `unsafe-inline` for style-src is required by Tailwind CDN and the
            // Filament theme; script-src stays free of it apart from the
            // deliberately-configurable analytics setting.
            'Content-Security-Policy' => implode('; ', [
                "default-src 'self'",
                "base-uri 'self'",
                "form-action 'self'",
                "frame-ancestors 'self'",
                "object-src 'none'",
                "img-src 'self' data: blob: https:",
                "font-src 'self' data: https://fonts.gstatic.com",
                "style-src 'self' 'unsafe-inline' https://fonts.googleapis.com https://cdn.jsdelivr.net https://unpkg.com",
                // Tailwind Play CDN, unpkg (Leaflet) and the youtube embeds.
                "script-src 'self' 'unsafe-inline' https://cdn.tailwindcss.com https://unpkg.com https://www.youtube.com https://youtube.com",
                "frame-src 'self' https://www.youtube.com https://youtube.com",
                "connect-src 'self'",
                "media-src 'self' blob:",
            ]),
        ];

        if ($request->isSecure()) {
            $headers['Strict-Transport-Security'] = 'max-age=31536000; includeSubDomains';
        }

        foreach ($headers as $name => $value) {
            if (! $response->headers->has($name)) {
                $response->headers->set($name, $value);
            }
        }

        return $response;
    }
}