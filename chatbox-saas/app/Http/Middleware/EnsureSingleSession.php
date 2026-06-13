<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class EnsureSingleSession
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
            $currentSessionId = Session::getId();

            // Se o ID da sessão atual for diferente do registrado no usuário
            if ($user->current_session_id && $user->current_session_id !== $currentSessionId) {
                // Se o login foi via "Remember Me", confiamos no dispositivo e sincronizamos a sessão
                if (Auth::viaRemember()) {
                    $user->forceFill(['current_session_id' => $currentSessionId])->save();

                    // Registra no log de sessões (opcional)
                    DB::table('user_session_logs')->insert([
                        'user_id' => $user->id,
                        'session_id' => $currentSessionId,
                        'ip_address' => $request->ip(),
                        'user_agent' => $request->header('User-Agent'),
                        'login_at' => now(),
                        'last_activity_at' => now(),
                        'is_active' => true,
                        'created_at' => now(),
                        'updated_at' => now(),
                    ]);
                } else {
                    // Caso contrário, é um conflito real de sessões (outro dispositivo entrou depois)
                    Auth::logout();
                    Session::flush();

                    if ($request->ajax() || $request->wantsJson()) {
                        return response()->json([
                            'message' => 'Sua sessão foi encerrada porque você entrou em outro dispositivo.',
                            'redirect' => route('filament.admin.auth.login'),
                        ], 401);
                    }

                    return redirect()->route('filament.admin.auth.login')->with('error', 'Sua sessão foi encerrada porque você entrou em outro dispositivo.');
                }
            }

            // Se o usuário não tem sessão registrada (ex: primeiro login ou limpou DB), sincroniza
            if (! $user->current_session_id) {
                $user->forceFill(['current_session_id' => $currentSessionId])->save();
            }

            // Atualiza a última atividade no log
            DB::table('user_session_logs')
                ->where('session_id', $currentSessionId)
                ->update(['last_activity_at' => now()]);
        }

        return $next($request);
    }
}
