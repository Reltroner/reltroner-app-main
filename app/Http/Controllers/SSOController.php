<?php
// app/Http/Controllers/SSOController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use App\Services\SSO\KeycloakIdentityService;

class SSOController extends Controller
{
    /**
     * Redirect user to Keycloak Authorization Endpoint
     */
    public function redirect(Request $request)
    {
        if (session('sso_authenticated') === true) {
            return redirect()->route('dashboard');
        }

        if (session()->has('sso_state')) {
            $state = session('sso_state');

            Log::info('SSO continuing existing flow', [
                'session_id' => session()->getId(),
                'state'      => $state,
            ]);
        } else {
            $state = bin2hex(random_bytes(32)); // stronger entropy
            session(['sso_state' => $state]);

            Log::info('SSO redirect issued', [
                'session_id' => session()->getId(),
                'state'      => $state,
            ]);
        }

        $query = http_build_query([
            'client_id'     => config('services.keycloak.client_id'),
            'response_type' => 'code',
            'scope'         => 'openid',
            'redirect_uri'  => config('services.keycloak.redirect_uri'),
            'state'         => $state,
        ]);

        return redirect()->away(
            rtrim(config('services.keycloak.base_url'), '/')
            . '/realms/' . config('services.keycloak.realm')
            . '/protocol/openid-connect/auth?' . $query
        );
    }

    /**
     * Handle callback from Keycloak
     */
    public function callback(Request $request, KeycloakIdentityService $identity)
    {
        if (!session()->has('sso_state')) {
            Log::error('SSO callback without active state', [
                'session_id' => session()->getId(),
                'state_query' => $request->state,
            ]);

            abort(403, 'SSO state expired.');
        }

        abort_if(!$request->has('code'), 403, 'Missing authorization code.');

        Log::info('SSO callback received', [
            'session_id'    => session()->getId(),
            'state_query'   => $request->state,
            'state_session' => session('sso_state'),
        ]);

        abort_if(
            !$request->has('state') || $request->state !== session('sso_state'),
            403,
            'Invalid SSO state.'
        );

        try {

            // 🔐 Exchange code for tokens (via service)
            $tokens = $identity->exchangeCode(
                $request->code,
                config('services.keycloak.redirect_uri')
            );

            abort_if(!isset($tokens['id_token']), 403, 'ID token not returned.');

            // 🔎 Verify ID Token cryptographically
            $claims = $identity->verifyIdToken($tokens['id_token']);

            // 🔒 Anti session fixation
            $request->session()->regenerate();

            session([
                'sso_authenticated' => true,
                'access_token'      => $tokens['access_token'],
                'refresh_token'     => $tokens['refresh_token'] ?? null,
                'expires_at'        => now()
                    ->addSeconds($tokens['expires_in'] ?? 300)
                    ->timestamp,
                'identity'          => $claims,
                'gateway_auth_at'   => now()->timestamp,
            ]);

            // Cleanup state
            session()->forget('sso_state');

            Log::info('SSO session established', [
                'session_id' => session()->getId(),
                'sub'        => $claims['sub'] ?? null,
            ]);

            return redirect()->route('dashboard');

        } catch (\Throwable $e) {

            Log::error('SSO callback failed', [
                'error' => $e->getMessage(),
            ]);

            abort(403, 'SSO authentication failed.');
        }
    }
}
