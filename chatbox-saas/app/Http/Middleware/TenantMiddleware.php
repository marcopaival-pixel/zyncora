<?php

namespace App\Http\Middleware;

use App\Models\Company;
use App\Services\TenantService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TenantMiddleware
{
    public function __construct(
        protected TenantService $tenantService
    ) {}

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Tenta encontrar o slug da empresa na rota
        $slug = $request->route('slug') ?? $request->route('companySlug');

        if ($slug) {
            $company = Company::query()->where('slug', $slug)->first();

            if (! $company || $company->status !== 'active') {
                abort(403, 'Company is inactive or not found.');
            }

            // Bloqueio rigoroso se o plano expirou
            if ($company->expires_at && now()->isAfter($company->expires_at)) {
                abort(402, 'Sua assinatura expirou. Entre em contato com o financeiro.');
            }

            $this->tenantService->setCompany($company);
        }

        return $next($request);
    }
}
