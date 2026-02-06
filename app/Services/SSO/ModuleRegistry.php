<?php
// app/Services/SSO/ModuleRegistry.php
namespace App\Services\SSO;

class ModuleRegistry
{
    protected array $modules = [
        'finance' => [
            'audience' => 'finance.reltroner.test',
            'redirect' => 'http://finance.reltroner.test',
        ],
    ];

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->modules);
    }

    public function get(string $key): array
    {
        if (!$this->has($key)) {
            throw new \InvalidArgumentException("Module not registered: {$key}");
        }

        return $this->modules[$key];
    }

    public function all(): array
    {
        return $this->modules;
    }
}

