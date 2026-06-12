<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Illuminate\Support\Facades\Auth;

class CheckLegalDocumentsAcceptance
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && !$request->routeIs('legal.pending-acceptance') && !$request->routeIs('legal.accept-pending') && !$request->routeIs('filament.admin.auth.logout')) {
            $user = Auth::user();
            
            $activeDocuments = \App\Models\PlatformLegalDocument::where('is_active', true)->get();
            $pendingDocuments = [];

            foreach ($activeDocuments as $doc) {
                $hasAccepted = \App\Models\PlatformLegalConsent::where('user_id', $user->id)
                    ->where('platform_legal_document_id', $doc->id)
                    ->exists();

                if (!$hasAccepted) {
                    $pendingDocuments[] = $doc;
                }
            }

            if (count($pendingDocuments) > 0) {
                return redirect()->route('legal.pending-acceptance');
            }
        }

        return $next($request);
    }
}
