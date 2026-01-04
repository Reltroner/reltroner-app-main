<?php
// app/Services/SSO/ModuleTokenFactory.php
namespace App\Services\SSO;

use Firebase\JWT\JWT;
use Illuminate\Support\Str;

class ModuleTokenFactory
{
    public function make(array $context): string
    {
        $now = time();

        $payload = [
            'iss' => config('services.gateway.issuer'),
            'aud' => $this->audience($context['module']),
            'sub' => session('keycloak_sub'),
            'email' => session('keycloak_email'),

            'iat' => $now,
            'exp' => $now + 60,
            'jti' => (string) Str::uuid(),

            'ctx' => [
                'module' => $context['module'],
                'entry'  => $context['entry'] ?? null,
            ],
        ];

        return JWT::encode(
            $payload,
            config('services.gateway.signing_key'),
            'HS256'
        );

        dd(
            config('services.gateway.issuer'),
            config('services.modules.finance')
        );
    }

    protected function audience(string $module): string
    {
        return match ($module) {
            'finance' => 'finance.reltroner.com',
            default => abort(400, 'Unknown module'),
        };
    }
}
