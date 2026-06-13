<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckTrialStatus
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = Auth::user();

        if (! $user || ! $user->company_id) {
            return $next($request);
        }

        $company = $user->company;

        // Check and update trial status if expired
        $status = $company->verificarStatusAssinatura();

        if ($status === 'expired') {
            // Rotas permitidas quando expirado
            $allowedRoutes = [
                'filament.admin.pages.upgrade-plan',
                'filament.admin.pages.billing',
                'filament.admin.pages.premium-area',
                'filament.admin.auth.logout',
            ];

            // Permite também acessar a edição da própria empresa (Perfil)
            $isCompanyProfile = $request->routeIs('filament.admin.resources.companies.edit')
                                && $request->route('record') == $company->id;

            if (! in_array($request->route()->getName(), $allowedRoutes) && ! $isCompanyProfile) {
                // Redirecionar para página de Upgrade
                return redirect()->route('filament.admin.pages.upgrade-plan')
                    ->with('error', 'O seu período de teste expirou. Faça o upgrade para continuar utilizando o sistema.');
            }
        }

        return $next($request);
    }
}
