<?php

namespace App\Http\Middleware;

use App\Models\Company;
use Closure;
use Filament\Facades\Filament;
use Filament\Support\Facades\FilamentView;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\View;
use Symfony\Component\HttpFoundation\Response;

class WhiteLabelMiddleware
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $host = $request->getHost();
        $mainDomain = config('app.domain', 'zincora.com'); // Exemplo de domínio principal

        // Se não for o domínio principal, nem localhost
        if ($host !== $mainDomain && ! str_ends_with($host, 'localhost') && ! str_ends_with($host, '127.0.0.1')) {
            $company = Company::where('custom_domain', $host)->first();

            if ($company && $company->plan === 'enterprise') {
                // Registrar a empresa no request para uso global
                $request->attributes->set('white_label_company', $company);
                View::share('whiteLabelCompany', $company);

                // Tenta injetar dinamicamente as customizações no painel atual do Filament
                try {
                    $panel = Filament::getCurrentPanel();
                    if ($panel) {
                        if ($company->panel_logo_path) {
                            $panel->brandLogo(asset('storage/'.$company->panel_logo_path));
                        }
                        if ($company->favicon_path) {
                            $panel->favicon(asset('storage/'.$company->favicon_path));
                        }
                        if ($company->brand_color) {
                            // In Filament 3, you can register a render hook to inject CSS
                            FilamentView::registerRenderHook(
                                'panels::head.end',
                                fn (): string => '<style>:root { --primary-500: '.$company->brand_color.'; }</style>'
                            );
                        }
                    }
                } catch (\Exception $e) {
                    // Ignore filament context errors
                }
            } else {
                // Domínio apontado mas sem empresa válida, pode redirecionar para 404
                // abort(404);
            }
        }

        return $next($request);
    }
}
