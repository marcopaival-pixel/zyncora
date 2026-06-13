<?php

namespace App\Filament\Pages\Auth;

use App\Models\PasswordRecoveryAudit;
use App\Models\User;
use App\Notifications\PasswordChangedNotification;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\PasswordResetResponse;
use Filament\Notifications\Notification;
use Filament\Pages\Auth\PasswordReset\ResetPassword as BaseResetPassword;
use Illuminate\Auth\Events\PasswordReset;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\Validation\Rules\Password;

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

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                $this->getEmailFormComponent()
                    ->disabled(),
                $this->getPasswordFormComponent()
                    ->label('Nova Senha')
                    ->rules([
                        Password::min(8)
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

    public function resetPassword(): ?PasswordResetResponse
    {
        $data = $this->form->getState();
        $email = $data['email'];

        $status = \Illuminate\Support\Facades\Password::broker(Filament::auth()->getProvider()->name ?? config('auth.defaults.passwords'))->reset(
            $data,
            function (User $user, string $password) {
                $user->password = Hash::make($password);
                $user->setRememberToken(Str::random(60));
                $user->save();

                event(new PasswordReset($user));

                // Invalidar sessões ativas
                DB::table('sessions')
                    ->where('user_id', $user->id)
                    ->delete();

                // Auditoria de sucesso
                PasswordRecoveryAudit::create([
                    'user_id' => $user->id,
                    'email' => $user->email,
                    'ip_address' => request()->ip(),
                    'user_agent' => request()->userAgent(),
                    'action' => 'reset_success',
                    'status' => 'success',
                ]);

                // Enviar notificação de senha alterada
                $user->notify(new PasswordChangedNotification(request()->ip(), request()->userAgent()));
            },
        );

        if ($status === \Illuminate\Support\Facades\Password::PASSWORD_RESET) {
            Notification::make()
                ->title('Senha alterada com sucesso.')
                ->success()
                ->send();

            return app(PasswordResetResponse::class);
        }

        // Se falhou (ex: token inválido ou expirado)
        PasswordRecoveryAudit::create([
            'email' => $email,
            'ip_address' => request()->ip(),
            'user_agent' => request()->userAgent(),
            'action' => 'invalid_token',
            'status' => 'failed',
        ]);

        Notification::make()
            ->title(__($status))
            ->danger()
            ->send();

        return null;
    }
}
