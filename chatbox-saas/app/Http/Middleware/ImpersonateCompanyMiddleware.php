<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Models\User;
use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class ImpersonateCompanyMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (session()->has('impersonated_company_id') && Auth::check()) {
            /** @var User $user */
            $user = Auth::user();

            if ($user->role === User::ROLE_PLATFORM_ADMIN) {
                $impersonatedCompanyId = session('impersonated_company_id');
                
                // Configurar o modelo para modo impersonação
                $user->is_impersonating = true;
                $user->impersonation_level = session('impersonation_level', 'view_only');
                $user->original_company_id = $user->company_id;
                $user->company_id = $impersonatedCompanyId;

                // Definir o tenant na service global
                $company = Company::find($impersonatedCompanyId);
                if ($company) {
                    app(TenantService::class)->setCompany($company);
                }
            }
        }

        return $next($request);
    }
}
