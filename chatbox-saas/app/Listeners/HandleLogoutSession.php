<?php

namespace App\Listeners;

use Illuminate\Auth\Events\Logout;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;

class HandleLogoutSession
{
    /**
     * Handle the event.
     */
    public function handle(Logout $event): void
    {
        if ($event->user) {
            $sessionId = Session::getId();

            DB::table('user_session_logs')
                ->where('user_id', $event->user->id)
                ->where('session_id', $sessionId)
                ->update([
                    'is_active' => false,
                    'logout_at' => now()
                ]);

            // Limpa o current_session_id se for a sessão que está saindo
            if ($event->user->current_session_id === $sessionId) {
                $event->user->current_session_id = null;
                $event->user->save();
            }
        }
    }
}
