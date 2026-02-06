<?php
// tests/TestCase.php
namespace Tests;

use App\Models\User;
use Illuminate\Foundation\Testing\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    /**
     * Act as authenticated SSO user (Gateway-aware)
     */
    protected function actingAsSSO(User $user)
    {
        return $this
            ->withSession([
                'sso_authenticated' => true,
                'keycloak_sub'      => (string) $user->id,
                'keycloak_email'    => $user->email,
            ])
            ->actingAs($user);
    }
}
