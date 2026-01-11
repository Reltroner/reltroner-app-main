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
        // ✅ Jika sudah login → langsung dashboard
        if (session('sso_authenticated') === true) {
            return redirect()->route('dashboard');
        }

        // 🔁 Jika SSO sudah dimulai → LANJUTKAN redirect
        if (session()->has('sso_state')) {
            $state = session('sso_state');

            Log::info('SSO continuing existing flow', [
                'session_id' => session()->getId(),
                'state' => $state,
            ]);
        } else {
            // 🔐 INITIATE SSO (FIRST TIME)
            $state = bin2hex(random_bytes(16));
            session(['sso_state' => $state]);

            Log::info('SSO redirect issued', [
                'session_id' => session()->getId(),
                'state' => $state,
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
    public function callback(Request $request)
    {
        if (!session()->has('sso_state')) {
            Log::error('SSO callback without active state', [
                'session_id' => session()->getId(),
                'state_query' => $request->state,
            ]);

            abort(403, 'SSO state expired.');
        }

        abort_if(!$request->has('code'), 403, 'Missing authorization code.');

        // 🔍 DEBUG STATE (PENTING UNTUK AUDIT)
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

        // 🔒 LOGIN SUKSES → baru regenerate session (ANTI FIXATION)
        $request->session()->regenerate();

        session()->put('sso_authenticated', true);
        session()->put('gateway_auth_at', now()->timestamp);

        session([
            'sso_authenticated' => true,
            'access_token'      => $token['access_token'],
            'id_token'          => $token['id_token'] ?? null,
        ]);

        // 🧹 Cleanup
        session()->forget('sso_state');

        Log::info('SSO session established', [
            'session_id' => session()->getId(),
        ]);

        return redirect()->route('dashboard');
    }
}
