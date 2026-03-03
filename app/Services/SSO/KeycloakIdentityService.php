<?php
// app/Services/SSO/KeycloakIdentityService.php
namespace App\Services\SSO;

use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;
use Illuminate\Support\Facades\Http;
use Exception;

class KeycloakIdentityService
{
    protected string $baseUrl;
    protected string $realm;
    protected string $clientId;

    public function __construct()
    {
        $this->baseUrl  = rtrim(config('services.keycloak.base_url'), '/');
        $this->realm    = config('services.keycloak.realm');
        $this->clientId = config('services.keycloak.client_id');
    }

    protected function fetchJwks(): array
    {
        $response = Http::get(
            "{$this->baseUrl}/realms/{$this->realm}/protocol/openid-connect/certs"
        );

        if (!$response->successful()) {
            throw new Exception('Failed to fetch JWKS.');
        }

        return $response->json();
    }

    public function verifyIdToken(string $idToken): array
    {
        JWT::$leeway = 60;

        $publicKey = config('services.keycloak.test_public_key', '');

        if (!empty($publicKey)) {
            $decoded = JWT::decode(
                $idToken,
                new Key($publicKey, 'RS256') // jika test pakai RS256
            );

            return (array) $decoded;
        }

        $jwks = $this->fetchJwks();

        $decoded = JWT::decode(
            $idToken,
            JWK::parseKeySet($jwks, 'RS256')
        );

        return (array) $decoded;
    }

    public function exchangeCode(string $code): array
    {
        $response = Http::asForm()->post(
            "{$this->baseUrl}/realms/{$this->realm}/protocol/openid-connect/token",
            [
                'grant_type'    => 'authorization_code',
                'client_id'     => $this->clientId,
                'client_secret' => config('services.keycloak.client_secret'),
                'redirect_uri'  => config('services.keycloak.redirect_uri'),
                'code'          => $code,
            ]
        );

        if (!$response->successful()) {
            throw new \Exception('Token exchange failed.');
        }

        $data = $response->json();

        if (!isset($data['id_token'])) {
            throw new \Exception('ID token not returned.');
        }

        return $data;
    }
}