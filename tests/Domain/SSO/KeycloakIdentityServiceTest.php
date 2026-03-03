<?php
// tests/Domain/SSO/KeycloakIdentityServiceTest.php
namespace Tests\Domain\SSO;

use Tests\TestCase;
use Firebase\JWT\JWT;
use Illuminate\Support\Facades\Http;
use App\Services\SSO\KeycloakIdentityService;

class KeycloakIdentityServiceTest extends TestCase
{
protected string $privateKey = <<<KEY
-----BEGIN RSA PRIVATE KEY-----
MIIEpAIBAAKCAQEAzqXWiwxupCSvJzpuG1HsRvN7kYM/qfNwCuWHa3gwxObn2ymlgmEYBLETymFcpnSUsctNk6heAQ7EzxKQEiC5EvhczHxVn9Yx8RJWb1x1o1t4bm/FG6HC8K3opgDdGztqKqRR3YKHyCuXapnwXCfJOLLmObAun1vDLteA94ppIqh+apMI2vlA38nSxrdbidKdvUSsfxbVsgcuyo6edSxnl2xe50Tzw9uQWGWpZJYG1ChcxrFAxo0xO+ogzAm8h1Hn0pVITNrW2N7A2Qe6hw2yYB9H9n1tFoZT3zh0+BTtPlqvGjufH6G+jD/adJzi10BGSAdoo6gWQBaLxGc1dQc5sKXc5teLoI0lp4rWuIwoMvVJE9idh+NROm4tW7x1YgnPZXoqhwIDAQABAoIBAQCxZQzQ0jrISFRCGDpa2BkLomqKgkl0vvArkHWQBaLxGc1dQc5sKXc5teLoI0lp4rWuIwoMvVJE9idh+NROm4tW7x1YgnPZXoqhwIDAQAB
-----END RSA PRIVATE KEY-----
KEY;

protected string $publicKey = <<<KEY
-----BEGIN PUBLIC KEY-----
MIIBIjANBgkqhkiG9w0BAQEFAAOCAQ8AMIIBCgKCAQEAzqXWiwxupCSvJzpuG1HsRvN7kYM/qfNwCuWHa3gwxObn2ymlgmEYBLETymFcpnSUsctNk6heAQ7EzxKQEiC5EvhczHxVn9Yx8RJWb1x1o1t4bm/FG6HC8K3opgDdGztqKqRR3YKHyCuXapnwXCfJOLLmObAun1vDLteA94ppIqh+apMI2vlA38nSxrdbidKdvUSsfxbVsgcuyo6edSxnl2xe50Tzw9uQWGWpZJYG1ChcxrFAxo0xO+ogzAm8h1Hn0pVITNrW2N7A2Qe6hw2yYB9H9n1tFoZT3zh0+BTtPlqvGjufH6G+jD/adJzi10BGSAdoo6gWQBaLxGc1dQc5sKXc5teLoIwIDAQAB
-----END PUBLIC KEY-----
KEY;
    
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
        $privateKeyResource = openssl_pkey_get_private($this->privateKey);

        return JWT::encode(
            $claims,
            $privateKeyResource,
            'RS256'
        );
    }

    public function test_valid_id_token_passes()
    {
        Http::fake([
            '*' => Http::response($this->buildJwks())
        ]);

        config()->set('services.keycloak.test_public_key', $this->publicKey);

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