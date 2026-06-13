<?php

namespace App\Filament\Pages\Auth;

use Filament\Pages\Auth\PasswordReset\ResetPassword as BaseResetPassword;
use Illuminate\Contracts\Support\Htmlable;

class CustomResetPassword extends BaseResetPassword
{
    protected static string $view = 'filament.pages.auth.custom-reset-password';

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

    public function form(\Filament\Forms\Form $form): \Filament\Forms\Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent()
                    ->disabled(),
                $this->getPasswordFormComponent()
                    ->label('Nova Senha')
                    ->rules([
                        \Illuminate\Validation\Rules\Password::min(8)
                            ->letters()
                            ->mixedCase()
                            ->numbers()
                            ->symbols(),
                    ])
                    ->validationMessages([
                        'min' => 'A senha deve ter pelo menos 8 caracteres.',
                        'letters' => 'A senha deve conter letras.',
                        'mixed' => 'A senha deve conter letras maiúsculas e minúsculas.',
                        'numbers' => 'A senha deve conter pelo menos um número.',
                        'symbols' => 'A senha deve conter pelo menos um símbolo especial.',
                    ]),
                $this->getPasswordConfirmationFormComponent()
                    ->label('Confirmar Nova Senha'),
            ]);
    }

    public function resetPassword(): ?\Filament\Http\Responses\Auth\Contracts\PasswordResetResponse
    {
        $data = $this->form->getState();
        $email = $data['email'];

        $status = \Illuminate\Support\Facades\Password::broker(\Filament\Facades\Filament::auth()->getProvider()->name ?? config('auth.defaults.passwords'))->reset(
            $data,
            function (\App\Models\User $user, string $password) {
                $user->password = \Illuminate\Support\Facades\Hash::make($password);
                $user->setRememberToken(\Illuminate\Support\Str::random(60));
                $user->save();

                event(new \Illuminate\Auth\Events\PasswordReset($user));

                // Invalidar sessões ativas
                \Illuminate\Support\Facades\DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->delete();

                // Auditoria de sucesso
                \App\Models\PasswordRecoveryAudit::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'action' => 'reset_success',
                    'status' => 'success',
                ]);

                // Enviar notificação de senha alterada
                $user->notify(new \App\Notifications\PasswordChangedNotification(request()->ip(), request()->userAgent()));
            },
        );

        if ($status === \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            \Filament\Notifications\Notification::make()
                ->title('Senha alterada com sucesso.')
                ->success()
                ->send();

            return app(\Filament\Http\Responses\Auth\Contracts\PasswordResetResponse::class);
        }

        // Se falhou (ex: token inválido ou expirado)
        \App\Models\PasswordRecoveryAudit::create([
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'action' => 'invalid_token',
            'status' => 'failed',
        ]);

        \Filament\Notifications\Notification::make()
            ->title(__($status))
            ->danger()
            ->send();

        return null;
    }
}
