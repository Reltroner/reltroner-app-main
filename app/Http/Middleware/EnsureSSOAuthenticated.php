<?php
// app/Http/Middleware/EnsureSSOAuthenticated.php
namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SSO\KeycloakIdentityService;

class EnsureSSOAuthenticated
{
    public function handle(Request $request, Closure $next)
    {
        // Allow SSO endpoints
        if ($request->routeIs('sso.login', 'sso.callback')) {
            return $next($request);
        }

        // Not authenticated
        if (!session('sso_authenticated')) {
            return redirect()->route('sso.login');
        }

        // Missing expiry timestamp → treat as invalid session
        if (!session()->has('expires_at')) {
            $this->invalidateSession($request, 'Missing expiry timestamp.');
            return redirect()->route('sso.login');
        }

        // Check token expiry
        if (now()->timestamp >= session('expires_at')) {

            Log::info('SSO token expired, attempting refresh...', [
                'session_id' => session()->getId(),
            ]);

            // Try refresh if available
            if (session()->has('refresh_token')) {
                try {
                    $identity = app(KeycloakIdentityService::class);

                    $tokens = $identity->refreshToken(
                        session('refresh_token')
                    );

                    session([
                        'access_token'  => $tokens['access_token'],
                        'refresh_token' => $tokens['refresh_token'] ?? session('refresh_token'),
                        'expires_at'    => now()
                            ->addSeconds($tokens['expires_in'] ?? 300)
                            ->timestamp,
                    ]);

                    Log::info('SSO token refreshed successfully.', [
                        'session_id' => session()->getId(),
                    ]);

                } catch (\Throwable $e) {

                    Log::warning('SSO token refresh failed.', [
                        'error' => $e->getMessage(),
                    ]);

                    $this->invalidateSession($request, 'Refresh failed.');
                    return redirect()->route('sso.login');
                }
            } else {
                $this->invalidateSession($request, 'Token expired.');
                return redirect()->route('sso.login');
            }
        }

        return $next($request);
    }

    protected function invalidateSession(Request $request, string $reason): void
    {
        Log::info('SSO session invalidated.', [
            'session_id' => session()->getId(),
            'reason'     => $reason,
        ]);

        $request->session()->invalidate();
        $request->session()->regenerateToken();
    }
}
