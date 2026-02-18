<?php
// app/Services/SSO/KeycloakIdentityService.php
namespace App\Services\SSO;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Firebase\JWT\JWT;
use Firebase\JWT\JWK;
use Firebase\JWT\Key;
use Exception;

class KeycloakIdentityService
{
    protected string $baseUrl;
    protected string $realm;
    protected string $clientId;
    protected ?string $clientSecret;

    public function __construct()
    {
        $this->baseUrl     = rtrim(config('services.keycloak.base_url'), '/');
        $this->realm       = config('services.keycloak.realm');
        $this->clientId    = config('services.keycloak.client_id');
        $this->clientSecret = config('services.keycloak.client_secret');
    }

    /**
     * Exchange authorization code for tokens
     */
    public function exchangeCode(string $code, string $redirectUri): array
    {
        $response = Http::asForm()->post(
            "{$this->baseUrl}/realms/{$this->realm}/protocol/openid-connect/token",
            array_filter([
                'grant_type'    => 'authorization_code',
                'client_id'     => $this->clientId,
                'client_secret' => $this->clientSecret,
                'redirect_uri'  => $redirectUri,
                'code'          => $code,
            ])
        );

        if (!$response->successful()) {
            Log::error('Keycloak token exchange failed', [
                'status' => $response->status(),
                'body'   => $response->body(),
            ]);

            throw new Exception('Token exchange failed.');
        }

        return $response->json();
    }

    /**
     * Verify ID Token (JWT)
     */
    public function verifyIdToken(string $idToken): array
    {
        try {
            $jwks = $this->fetchJwks();

            $decoded = JWT::decode(
                $idToken,
                JWK::parseKeySet($jwks)
            );

            $payload = (array) $decoded;

            $this->validateClaims($payload);

            return $payload;

        } catch (Exception $e) {
            Log::error('ID Token verification failed', [
                'error' => $e->getMessage(),
            ]);

            throw new Exception('Invalid ID token.');
        }
    }

    /**
     * Validate standard OIDC claims
     */
    protected function validateClaims(array $claims): void
    {
        $issuer = "{$this->baseUrl}/realms/{$this->realm}";

        if (($claims['iss'] ?? null) !== $issuer) {
            throw new Exception('Invalid token issuer.');
        }

        if (($claims['aud'] ?? null) !== $this->clientId) {
            throw new Exception('Invalid token audience.');
        }

        if (!isset($claims['exp']) || $claims['exp'] < time()) {
            throw new Exception('Token expired.');
        }
    }

    /**
     * Fetch JWKS from Keycloak
     */
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
}
