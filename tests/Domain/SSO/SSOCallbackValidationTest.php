<?php
// tests/Domain/SSO/SSOCallbackValidationTest.php
namespace Tests\Domain\SSO;

use Tests\TestCase;

class SSOCallbackValidationTest extends TestCase
{
    public function test_callback_fails_without_state_in_session(): void
    {
        $response = $this->get('/sso/callback?code=abc&state=xyz');

        $response->assertStatus(403);
    }

    public function test_callback_fails_when_state_mismatch(): void
    {
        session(['sso_state' => 'correct']);

        $response = $this->get('/sso/callback?code=abc&state=wrong');

        $response->assertStatus(403);
    }

    public function test_callback_fails_when_code_missing(): void
    {
        session(['sso_state' => 'correct']);

        $response = $this->get('/sso/callback?state=correct');

        $response->assertStatus(403);
    }
}