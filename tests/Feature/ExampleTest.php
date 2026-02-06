<?php
// tests/Feature/ExampleTest.php
namespace Tests\Feature;

use Tests\TestCase;

class ExampleTest extends TestCase
{
    public function test_root_redirects_to_sso_login_for_guest(): void
    {
        $response = $this->get('/');

        $response->assertRedirect('/sso/login');
    }
}
