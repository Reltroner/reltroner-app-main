<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class BlockBotUserAgent
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $ua = $request->header('User-Agent');

        $blocked = [
            'axios',
            'lkk_ch.js',
            'twint_ch.js',
            'Java', // banyak bot tua pakai ini
            'Go-http-client',
        ];

        foreach ($blocked as $bot) {
            if (stripos($ua, $bot) !== false) {
                abort(403, 'Access Denied');
            }
        }
        
        return $next($request);
    }
}
