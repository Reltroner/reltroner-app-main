<?php
// tests/Domain/Redirect/FinanceRedirectGuardTest.php
namespace Tests\Domain\Redirect;

use Tests\TestCase;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;

class FinanceRedirectGuardTest extends TestCase
{
    use RefreshDatabase;
    public function test_finance_redirect_requires_authenticated_user(): void
    {
        $response = $this->get('/modules/finance');

        $response->assertRedirect('/sso/login');
    }

    public function test_finance_redirect_works_for_authenticated_user(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)
            ->get('/modules/finance');

        $response->assertRedirect(); // redirect to finance module
    }
}
