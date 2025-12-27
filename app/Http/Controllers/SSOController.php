<?php
// app/Http/Controllers/SSOController.php
namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;

class SSOController extends Controller
{
    public function redirect()
    {
        $query = http_build_query([
            'client_id'     => config('services.keycloak.client_id'),
            'response_type' => 'code',
            'scope'         => 'openid',
            'redirect_uri'  => config('services.keycloak.redirect_uri'),
        ]);

        return redirect(
            config('services.keycloak.base_url')
            . '/realms/' . config('services.keycloak.realm')
            . '/protocol/openid-connect/auth?' . $query
        );
    }

    public function callback(Request $request)
    {
        abort_if(!$request->has('code'), 403);

        $token = Http::asForm()->post(
            config('services.keycloak.base_url')
            . '/realms/' . config('services.keycloak.realm')
            . '/protocol/openid-connect/token',
            [
                'grant_type'   => 'authorization_code',
                'client_id'    => config('services.keycloak.client_id'),
                'redirect_uri' => config('services.keycloak.redirect_uri'),
                'code'         => $request->code,
            ]
        )->json();

        abort_if(!isset($token['access_token']), 403);

        // 🔑 AUTH GATEWAY SESSION
        session([
            'sso_authenticated' => true,
            'access_token'      => $token['access_token'],
            'id_token'          => $token['id_token'] ?? null,
        ]);

        return redirect()->route('dashboard');
    }
}
