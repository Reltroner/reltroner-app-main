<?php
// app/Http/Middleware/EnsureSSOAuthenticated.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class EnsureSSOAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        if (!session('sso_authenticated')) {
            return redirect()->route('sso.login');
        }

        return $next($request);
    }
}
