<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Symfony\Component\HttpFoundation\Response;

class SessionTimeoutMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (Auth::check()) {
            $user = Auth::user();
            $lastActivity = session('last_activity_time');
            $timeout = config('session.lifetime') * 60; // em segundos

            if ($lastActivity && (time() - $lastActivity > $timeout)) {
                Auth::logout();
                session()->flush();

                if ($request->ajax() || $request->wantsJson()) {
                    return response()->json([
                        'message' => 'Sua sessão expirou por inatividade.',
                        'redirect' => route('filament.admin.auth.login')
                    ], 401);
                }

                return redirect()->route('filament.admin.auth.login')->with('error', 'Sua sessão expirou por inatividade.');
            }

            session(['last_activity_time' => time()]);
            
            // Atualiza last_active_at no banco (evita muitas escritas, fazemos a cada 5 min)
            if (!$user->last_active_at || $user->last_active_at->diffInMinutes(now()) >= 5) {
                $user->update(['last_active_at' => now()]);
            }
        }

        return $next($request);
    }
}
