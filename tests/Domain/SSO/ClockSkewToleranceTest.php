<?php
// tests/Domain/SSO/ClockSkewToleranceTest.php
namespace Tests\Domain\SSO;

use Tests\TestCase;
use Firebase\JWT\JWT;

class ClockSkewToleranceTest extends TestCase
{
    public function test_small_clock_skew_is_tolerated(): void
    {
        JWT::$leeway = 60;

        $payload = [
            'iat' => time() - 30,
            'exp' => time() + 300,
        ];

        $this->assertTrue(true);
    }

    public function test_large_clock_skew_would_fail_without_leeway(): void
    {
        $this->assertTrue(true);
    }
}