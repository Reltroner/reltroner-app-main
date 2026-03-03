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

        $state = session('sso_state');

        if (!$state) {
            $state = bin2hex(random_bytes(32));
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
        abort_if(!session()->has('sso_state'), 403, 'SSO state expired.');
        abort_if(!$request->has('code'), 403, 'Missing authorization code.');
        abort_if(
            !$request->has('state') || $request->state !== session('sso_state'),
            403,
            'Invalid SSO state.'
        );

        try {

            $tokens = $identity->exchangeCode(
                $request->code,
                config('services.keycloak.redirect_uri')
            );

            abort_if(!isset($tokens['id_token']), 403, 'ID token not returned.');

            $claims = $identity->verifyIdToken($tokens['id_token']);

            $request->session()->regenerate();

            session([
                'sso_authenticated' => true,
                'access_token'      => $tokens['access_token'],
                'refresh_token'     => $tokens['refresh_token'] ?? null,
                'id_token'          => $tokens['id_token'], // ✅ FIXED
                'expires_at'        => now()
                    ->addSeconds($tokens['expires_in'] ?? 300)
                    ->timestamp,
                'identity'          => $claims,
                'gateway_auth_at'   => now()->timestamp,
            ]);

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

    /**
     * OIDC RP-Initiated Logout
     */
    public function logout(Request $request)
    {
        Log::info('LOGOUT CONTROLLER EXECUTED');

        $idToken = session('id_token');

        // Always build redirect target FIRST (before invalidating session)
        $postLogoutRedirect = route('logged.out');

        if (!$idToken) {
            $request->session()->invalidate();
            $request->session()->regenerateToken();
            return redirect($postLogoutRedirect);
        }

        $logoutUrl = rtrim(config('services.keycloak.base_url'), '/')
            . '/realms/' . config('services.keycloak.realm')
            . '/protocol/openid-connect/logout';

        $query = http_build_query([
            'id_token_hint'            => $idToken,
            'post_logout_redirect_uri' => $postLogoutRedirect,
            'client_id'                => config('services.keycloak.client_id'),
        ]);

        Log::info('Logout redirect target', [
            'post_logout_redirect_uri' => $postLogoutRedirect,
        ]);

        return redirect()->away($logoutUrl . '?' . $query);
    }

    /**
     * Final local cleanup after Keycloak logout
     */
    public function loggedOut(Request $request)
    {
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('sso.login');
    }
}