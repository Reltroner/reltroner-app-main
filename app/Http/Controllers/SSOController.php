<?php
// app/Http/Controllers/SSOController.php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class SSOController extends Controller
{
    /**
     * Redirect user to Keycloak Authorization Endpoint
     */
    public function redirect(Request $request)
    {
        // 🔒 Pastikan session stabil sebelum OIDC flow
        $request->session()->regenerate();

        // 🔒 LOCK redirect_uri
        $redirectUri = rtrim(config('services.keycloak.redirect_uri'), '/');

        // 🧠 CSRF-like protection
        $state = bin2hex(random_bytes(16));
        session(['sso_state' => $state]);

        $query = http_build_query([
            'client_id'     => config('services.keycloak.client_id'),
            'response_type' => 'code',
            'scope'         => 'openid',
            'redirect_uri'  => $redirectUri,
            'state'         => $state,
        ]);

        $authUrl =
            rtrim(config('services.keycloak.base_url'), '/')
            . '/realms/' . config('services.keycloak.realm')
            . '/protocol/openid-connect/auth?' . $query;

        return redirect()->away($authUrl);
    }

    /**
     * Handle callback from Keycloak
     */
    public function callback(Request $request)
    {
        // ❌ No code → reject
        abort_if(!$request->has('code'), 403, 'Missing authorization code.');

        // ❌ State mismatch → reject
        abort_if(
            !$request->has('state') || $request->state !== session('sso_state'),
            403,
            'Invalid SSO state.'
        );

        // 🔒 LOCK redirect_uri (HARUS IDENTIK dgn yg di auth request)
        $redirectUri = rtrim(config('services.keycloak.redirect_uri'), '/');

        $tokenResponse = Http::asForm()->post(
            rtrim(config('services.keycloak.base_url'), '/')
            . '/realms/' . config('services.keycloak.realm')
            . '/protocol/openid-connect/token',
            [
                'grant_type'   => 'authorization_code',
                'client_id'    => config('services.keycloak.client_id'),
                'redirect_uri' => $redirectUri,
                'code'         => $request->code,
            ]
        );

        if (!$tokenResponse->successful()) {
            Log::error('Keycloak token exchange failed', [
                'status' => $tokenResponse->status(),
                'body'   => $tokenResponse->json(),
            ]);

            abort(403, 'Failed to exchange token with Keycloak.');
        }

        $token = $tokenResponse->json();

        abort_if(!isset($token['access_token']), 403, 'Access token not returned.');

        // 🔑 AUTH GATEWAY SESSION (TANPA DB, TANPA USER)
        session([
            'sso_authenticated' => true,
            'access_token'      => $token['access_token'],
            'id_token'          => $token['id_token'] ?? null,
        ]);

        Log::info('SSO redirect issued', [
            'session_id' => session()->getId(),
        ]);

        Log::info('SSO callback received', [
            'session_id'     => session()->getId(),
            'state_query'    => $request->state,
            'state_session'  => session('sso_state'),
        ]);

        // Cleanup
        session()->forget('sso_state');

        return redirect()->route('dashboard');
    }
}
