<?php
// tests/Domain/SSO/KeycloakIdentityServiceTest.php
namespace Tests\Domain\SSO;

use Tests\TestCase;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use App\Services\SSO\KeycloakIdentityService;

class KeycloakIdentityServiceTest extends TestCase
{

    protected function buildJwks(): array
    {
        return [
            'keys' => [
                [
                    'kty' => 'RSA',
                    'kid' => 'test-key',
                    'use' => 'sig',
                    'alg' => 'RS256',
                    'n' => '0tL_UFhlqWSWBtQoXMaxQMaNMTvqIMwJvIdR59KVSEza1tjewNwNkHuocNsmAfR_Z9bRaGU984dPgU7T5arxo7nx-hvow_2nSc4tdARkgHaKOoFkAWi8RnNXUHObCl3ObXi6CNJaeK1riMKDL1SRPYnYfjUTpuLVu8dWIJz2V6Koc',
                    'e' => 'AQAB',
                ]
            ]
        ];
    }

    protected function issueToken(array $claims): string
    {
        $privateKey = file_get_contents(
            base_path('tests/Fixtures/private.pem')
        );

        return JWT::encode(
            $claims,
            $privateKey,
            'RS256'
        );
    }

    public function test_valid_id_token_passes()
    {
        Http::fake([
            '*' => Http::response($this->buildJwks())
        ]);

        config()->set(
            'services.keycloak.test_public_key',
            file_get_contents(base_path('tests/Fixtures/public.pem'))
        );

        $service = new KeycloakIdentityService();

        $token = $this->issueToken([
            'iss' => config('services.keycloak.base_url') . '/realms/' . config('services.keycloak.realm'),
            'aud' => [config('services.keycloak.client_id')],
            'exp' => time() + 300,
            'iat' => time(),
            'sub' => 'test-user',
        ]);

        $payload = $service->verifyIdToken($token);

        $this->assertArrayHasKey('exp', $payload);
    }
}