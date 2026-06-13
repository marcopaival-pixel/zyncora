<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class CheckOnboardingStatus
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();

            // Ignorar para admins do sistema (opcional)
            if ($user->role === 'platform_admin') {
                return $next($request);
            }

            // Rotas isentas do bloqueio
            $exemptRoutes = [
                'filament.admin.pages.onboarding-wizard',
                'filament.admin.auth.logout',
                'legal.pending-acceptance',
                'legal.accept-pending',
                'livewire.update', // Permitir requisições livewire do wizard
            ];

            if ($user->company && ! $user->company->is_onboarding_completed) {
                if (! in_array($request->route()?->getName(), $exemptRoutes)) {
                    return redirect()->route('filament.admin.pages.onboarding-wizard');
                }
            }
        }

        return $next($request);
    }
}
