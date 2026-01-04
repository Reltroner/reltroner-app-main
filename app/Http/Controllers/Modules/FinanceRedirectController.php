<?php
// app/Http/Controllers/Modules/FinanceRedirectController.php
namespace App\Http\Controllers\Modules;

use App\Http\Controllers\Controller;
use App\Services\SSO\ModuleTokenFactory;
use Illuminate\Http\Request;

class FinanceRedirectController extends Controller
{
    public function __invoke(Request $request, ModuleTokenFactory $factory)
    {
        if (!session('sso_authenticated')) {
            abort(403);
        }

        $token = $factory->make([
            'module' => 'finance',
            'entry'  => 'dashboard',
        ]);

        $target = rtrim(config('services.modules.finance'), '/');

        return redirect()->away(
            $target . '/sso/consume?token=' . urlencode($token)
        );
    }
}
