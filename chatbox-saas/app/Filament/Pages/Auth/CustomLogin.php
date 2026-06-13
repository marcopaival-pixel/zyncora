<?php

namespace App\Filament\Pages\Auth;

use App\Models\User;
use App\Services\WebLoginSessionService;
use Filament\Facades\Filament;
use Filament\Forms\Form;
use Filament\Http\Responses\Auth\Contracts\LoginResponse;
use Filament\Pages\Auth\Login as BaseLogin;
use Illuminate\Contracts\Support\Htmlable;
use Illuminate\Validation\ValidationException;

class CustomLogin extends BaseLogin
{
    protected static string $view = 'filament.pages.auth.custom-login';

    /**
     * Evita o cabeçalho padrão (logo da marca + «Faça login») que duplicava o layout;
     * o conteúdo fica só na view customizada, com Empresa.png no topo.
     */
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
                    ->label('E-mail de Acesso')
                    ->prefixIcon('heroicon-m-envelope')
                    ->placeholder('exemplo@zynkora.com.br')
                    ->extraInputAttributes(['class' => 'transition-all duration-300']),
                $this->getPasswordFormComponent()
                    ->label('Senha de Segurança')
                    ->prefixIcon('heroicon-m-lock-closed')
                    ->revealable()
                    ->hint('Utilize a senha definida para a sua conta.')
                    ->extraInputAttributes(['class' => 'transition-all duration-300']),
                $this->getRememberFormComponent()
                    ->label('Lembrar meu acesso neste dispositivo'),
            ])
            ->statePath('data');
    }

    protected function throwFailureValidationException(): never
    {
        throw ValidationException::withMessages([
            'data.email' => __('filament-panels::pages/auth/login.messages.failed'),
        ]);
    }

    /**
     * Após o login bem-sucedido, o Filament chama {@see session()->regenerate()}.
     * Sincroniza {@see User::$current_session_id} com o ID final da sessão para o
     * {@see \App\Http\Middleware\EnsureSingleSession} não desligar o utilizador de seguida.
     */
    public function authenticate(): ?LoginResponse
    {
        $response = parent::authenticate();

        if ($response !== null && Filament::auth()->check()) {
            $user = Filament::auth()->user();
            if ($user instanceof User) {
                app(WebLoginSessionService::class)->syncAfterFilamentLogin($user);

                // Redireciona o SuperAdmin para o painel correto caso ele tenha logado pelo painel padrão
                if ($user->isPlatformAdmin() && Filament::getCurrentPanel()->getId() !== 'super-admin' && !$user->is_impersonating) {
                    return new class implements \Filament\Http\Responses\Auth\Contracts\LoginResponse {
                        public function toResponse($request)
                        {
                            return redirect()->to(Filament::getPanel('super-admin')->getUrl());
                        }
                    };
                }
            }
        }

        return $response;
    }
}
