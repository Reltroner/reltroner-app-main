<?php
// app/Http/Middleware/EnsureSSOAuthenticated.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSSOAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        // Allow SSO endpoints unconditionally
        if ($request->routeIs('sso.login', 'sso.callback')) {
            return $next($request);
        }

        if (session('sso_authenticated') === true) {
            return $next($request);
        }

        return redirect()->route('sso.login');
    }
}

