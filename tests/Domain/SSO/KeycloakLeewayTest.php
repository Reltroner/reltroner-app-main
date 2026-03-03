<?php
// tests/Domain/SSO/KeycloakLeewayTest.php
namespace Tests\Domain\SSO;

use Tests\TestCase;
use Firebase\JWT\JWT;

class KeycloakLeewayTest extends TestCase
{
    public function test_jwt_leeway_is_set_to_60_seconds(): void
    {
        JWT::$leeway = 60;

        $this->assertEquals(60, JWT::$leeway);
    }
}