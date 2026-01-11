<?php
// gateway app/Services/SSO/ModuleTokenFactory.php
namespace App\Services\SSO;

use Firebase\JWT\JWT;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Config;
use RuntimeException;

class ModuleTokenFactory
{
    /**
     * Issue short-lived JWT for ERP module consumption
     */
    public function make(array $context): string
    {
        if (!session('sso_authenticated')) {
            throw new RuntimeException('Gateway session not authenticated.');
        }

        $now = time();

        $payload = [
            'iss' => Config::get('services.gateway.issuer'),
            'aud' => $this->audience($context['module']),
            'sub' => session('keycloak_sub'),
            'email' => session('keycloak_email'),

            'iat' => $now,
            'exp' => $now + 60, // 60s — intentional (handoff only)
            'jti' => (string) Str::uuid(),

            'ctx' => [
                'module' => $context['module'],
                'entry'  => $context['entry'] ?? null,
            ],
        ];

        return JWT::encode(
            $payload,
            Config::get('services.gateway.signing_key'),
            'HS256'
        );
    }

    /**
     * Resolve module audience
     */
    protected function audience(string $module): string
    {
        return match ($module) {
            'finance' => parse_url(
                Config::get('services.modules.finance'),
                PHP_URL_HOST
            ),
            default => throw new RuntimeException("Unknown module audience [$module]"),
        };
    }
}
