<?php
// tests/Domain/SSO/ModuleRegistryTest.php
namespace Tests\Domain\SSO;

use Tests\TestCase;
use App\Services\SSO\ModuleRegistry;

class ModuleRegistryTest extends TestCase
{
    public function test_only_registered_modules_are_allowed(): void
    {
        $registry = app(ModuleRegistry::class);

        $this->assertTrue($registry->has('finance'));
        $this->assertFalse($registry->has('unknown-module'));
    }

    public function test_module_metadata_is_deterministic(): void
    {
        $registry = app(ModuleRegistry::class);

        $finance = $registry->get('finance');

        $this->assertArrayHasKey('audience', $finance);
        $this->assertArrayHasKey('redirect', $finance);
    }
}
