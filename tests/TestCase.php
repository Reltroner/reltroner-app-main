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
    protected function actingAsSSO($user)
    {
        $this->actingAs($user);

        $this->withSession([
            'sso_authenticated' => true,
            'access_token'      => 'fake-access-token',
            'refresh_token'     => 'fake-refresh-token',
            'expires_at'        => now()->addHour()->timestamp,
            'identity'          => [
                'sub' => $user->id,
                'aud' => config('services.keycloak.client_id'),
                'iss' => config('services.keycloak.base_url') . '/realms/' . config('services.keycloak.realm'),
                'exp' => now()->addHour()->timestamp,
            ],
            'gateway_auth_at'   => now()->timestamp,
        ]);

        return $this;
    }
}
