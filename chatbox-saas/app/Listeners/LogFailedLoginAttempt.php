<?php

namespace App\Listeners;

use App\Models\ActivityLog;
use App\Models\User;
use Illuminate\Auth\Events\Failed;

class LogFailedLoginAttempt
{
    /**
     * Handle the event.
     */
    public function handle(Failed $event): void
    {
        $email = $event->credentials['email'] ?? 'desconhecido';

        // Tenta encontrar o usuário para associar ao log e à empresa
        $user = $event->user ?? User::where('email', $email)->first();

        ActivityLog::create([
            'user_id' => $user?->id,
            'company_id' => $user?->company_id,
            'event' => 'login_failed',
            'description' => "Tentativa de login falha para o e-mail: {$email}",
            'ip_address' => request()->ip(),
            'user_agent' => request()->header('User-Agent'),
            'properties' => [
                'email' => $email,
                'method' => 'filament_login',
            ],
        ]);
    }
}
