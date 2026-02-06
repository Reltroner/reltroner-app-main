<?php
// tests/Domain/Middleware/EnsureSSOAuthenticatedTest.php
namespace Tests\Domain\Middleware;

use Tests\TestCase;

class EnsureSSOAuthenticatedTest extends TestCase
{
    public function test_unauthenticated_user_is_redirected(): void
    {
        $response = $this->get('/dashboard');

        $response->assertRedirect('/sso/login');
    }
}
