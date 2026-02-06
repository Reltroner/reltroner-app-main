<?php
// tests/Domain/SSO/ModuleTokenFactoryTest.php
namespace Tests\Domain\SSO;

use App\Services\SSO\ModuleTokenFactory;
use Firebase\JWT\JWT;
use Firebase\JWT\Key;
use Tests\TestCase;

class ModuleTokenFactoryTest extends TestCase
{
    public function test_token_is_signed_and_contains_required_claims(): void
    {
        // Simulate SSO-authenticated gateway session
        $this->withSession([
            'sso_authenticated' => true,
            'keycloak_sub'      => 'user-123',
            'keycloak_email'    => 'user@example.com',
        ]);

        $factory = app(ModuleTokenFactory::class);

        $token = $factory->make([
            'module' => 'finance',
            'entry'  => '/dashboard',
        ]);

        $this->assertIsString($token);

        // Decode token
        $decoded = JWT::decode(
            $token,
            new Key(config('services.gateway.signing_key'), 'HS256')
        );

        $this->assertSame('user-123', $decoded->sub);
        $this->assertSame('user@example.com', $decoded->email);
        $this->assertSame('finance', $decoded->ctx->module);

        $this->assertObjectHasProperty('iss', $decoded);
        $this->assertObjectHasProperty('aud', $decoded);
        $this->assertObjectHasProperty('iat', $decoded);
        $this->assertObjectHasProperty('exp', $decoded);
        $this->assertObjectHasProperty('jti', $decoded);
    }
}
