<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class CompanyImpersonatedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public string $adminName,
        public string $reason
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Acesso Administrativo ao Seu Ambiente - Zynkora')
            ->greeting('Olá!')
            ->line('Um administrador da plataforma acessou a sua conta para fins de suporte e auditoria.')
            ->line('**Administrador:** ' . $this->adminName)
            ->line('**Motivo:** ' . $this->reason)
            ->line('**Data e Hora:** ' . now()->format('d/m/Y H:i:s'))
            ->line('Se você não solicitou suporte ou desconhece este acesso, por favor entre em contato com nossa equipe imediatamente.')
            ->action('Acessar Painel', url('/admin'))
            ->salutation('Equipe de Suporte Zynkora');
    }

    public function toArray(object $notifiable): array
    {
        return [];
    }
}
