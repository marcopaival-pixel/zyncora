<?php

namespace App\Http\Middleware;

use App\Models\PlatformLegalConsent;
use App\Models\PlatformLegalDocument;
use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class CheckLegalDocumentsAcceptance
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check() && ! $request->routeIs('legal.pending-acceptance') && ! $request->routeIs('legal.accept-pending') && ! $request->routeIs('filament.admin.auth.logout')) {
            $user = Auth::user();

            $hasPendingDocuments = Cache::remember("user_{$user->id}_has_pending_legal_docs", now()->addHours(12), function () use ($user) {
                $activeDocuments = PlatformLegalDocument::where('is_active', true)->get();
                if ($activeDocuments->isEmpty()) {
                    return false;
                }

                $acceptedDocIds = PlatformLegalConsent::where('user_id', $user->id)
                    ->pluck('platform_legal_document_id')
                    ->toArray();

                foreach ($activeDocuments as $doc) {
                    if (! in_array($doc->id, $acceptedDocIds)) {
                        return true; // Encontrou pelo menos um não aceite
                    }
                }

                return false;
            });

            if ($hasPendingDocuments) {
                return redirect()->route('legal.pending-acceptance');
            }
        }

        return $next($request);
    }
}
