<?php

namespace App\Filament\Pages\Auth;

use App\Models\PasswordRecoveryAudit;
use App\Models\User;
use Filament\Facades\Filament;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\PasswordReset\RequestPasswordReset as BaseRequestPasswordReset;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\Password;
use Illuminate\Support\Facades\RateLimiter;

class CustomRequestPasswordReset extends BaseRequestPasswordReset
{
    protected static string $view = 'filament.pages.auth.custom-request-password-reset';

    public function hasLogo(): bool
    {
        return false;
    }

    public function getHeading(): string|Htmlable
    {
        return '';
    }

    public function getSubheading(): string|Htmlable|null
    {
        return null;
    }

    public function request(): void
    {
        $data = $this->form->getState();
        $email = $data['email'];
        $ip = request()->ip();

        // Rate Limit: 3 tentativas por hora por IP
        $rateLimitKey = 'password_reset_request_'.$ip;
        if (RateLimiter::tooManyAttempts($rateLimitKey, 3)) {
            PasswordRecoveryAudit::create([
                'email' => $email,
                'ip_address' => $ip,
                'user_agent' => request()->userAgent(),
                'action' => 'rate_limited',
                'status' => 'failed',
            ]);

            Notification::make()
                ->title('Muitas tentativas')
                ->body('Você realizou muitas solicitações recentemente. Tente novamente mais tarde.')
                ->danger()
                ->send();

            return;
        }

        RateLimiter::hit($rateLimitKey, 3600);

        $user = User::where('email', $email)->first();

        // Sempre registramos a solicitação, quer o usuário exista ou não.
        PasswordRecoveryAudit::create([
            'user_id' => $user?->id,
            'email' => $email,
            'ip_address' => $ip,
            'user_agent' => request()->userAgent(),
            'action' => 'requested',
            'status' => 'success',
        ]);

        if ($user) {
            $status = Password::broker(Filament::auth()->getProvider()->name ?? config('auth.defaults.passwords'))
                ->sendResetLink(['email' => $email]);
        }

        // Mensagem Genérica de Sucesso para prevenir enumeração de contas
        $this->form->fill();

        Notification::make()
            ->title('Solicitação Enviada')
            ->body('Se existir uma conta vinculada a este e-mail, enviaremos instruções para redefinição da senha em instantes.')
            ->success()
            ->send();
    }
}
