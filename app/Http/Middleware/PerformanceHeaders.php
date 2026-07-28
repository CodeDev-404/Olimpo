<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class PerformanceHeaders
{
    public function handle(Request $request, Closure $next)
    {
        $response = $next($request);

        $path = $request->path();

        if (str_starts_with($path, 'vendor/') || str_starts_with($path, 'build/')) {
            $response->headers->set('Cache-Control', 'public, max-age=31536000, immutable');
        } elseif (preg_match('/\.(css|js|png|jpg|jpeg|gif|ico|svg|woff|woff2|ttf|eot)$/', $path)) {
            $response->headers->set('Cache-Control', 'public, max-age=2592000');
        }

        $isLivewire = str_starts_with($path, 'livewire/');
        if ($isLivewire) {
            $response->headers->set('Cache-Control', 'no-cache, no-store, must-revalidate');
            $response->headers->set('X-Livewire', 'true');
        }

        $type = $response->headers->get('Content-Type', '');
        if (str_contains($type, 'text/html') || str_contains($type, 'text/css') || str_contains($type, 'application/javascript') || str_contains($type, 'text/javascript') || str_contains($type, 'application/json')) {
            $response->headers->set('Vary', 'Accept-Encoding');
        }

        if (!$isLivewire && str_contains($type, 'text/html') && str_contains($request->header('Accept-Encoding', ''), 'gzip')) {
            $content = $response->getContent();
            if (is_string($content) && strlen($content) > 0) {
                $encoded = gzencode($content, 6);
                if ($encoded !== false) {
                    $response->setContent($encoded);
                    $response->headers->set('Content-Encoding', 'gzip');
                    $response->headers->remove('Content-Length');
                }
            }
        }

        return $response;
    }
}
