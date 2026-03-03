<?php
// tests/Domain/SSO/SSOIdTokenRequirementTest.php
namespace Tests\Domain\SSO;

use Tests\TestCase;
use Mockery;
use App\Services\SSO\KeycloakIdentityService;

class SSOIdTokenRequirementTest extends TestCase
{
    public function test_callback_fails_when_id_token_not_returned(): void
    {
        session(['sso_state' => 'valid']);

        $mock = Mockery::mock(KeycloakIdentityService::class);
        $mock->shouldReceive('exchangeCode')
            ->once()
            ->andReturn([
                'access_token' => 'fake-access-token'
                // intentionally no id_token
            ]);

        $this->app->instance(KeycloakIdentityService::class, $mock);

        $response = $this->get('/sso/callback?code=abc&state=valid');

        $response->assertStatus(403);
    }
}