<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class PasswordChangedNotification extends Notification
{
    use Queueable;

    public $ipAddress;
    public $userAgent;

    /**
     * Create a new notification instance.
     */
    public function __construct($ipAddress = null, $userAgent = null)
    {
        $this->ipAddress = $ipAddress ?? request()->ip();
        $this->userAgent = $userAgent ?? request()->userAgent();
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject(Lang::get('Sua senha foi alterada com sucesso - Zynkora'))
            ->greeting('Olá, ' . $notifiable->name . '!')
            ->line(Lang::get('Sua senha foi alterada com sucesso.'))
            ->line(Lang::get('Detalhes da alteração:'))
            ->line(Lang::get('- Data/Hora: ') . now()->format('d/m/Y H:i:s'))
            ->line(Lang::get('- Endereço IP: ') . $this->ipAddress)
            ->line(Lang::get('- Navegador/Dispositivo: ') . $this->userAgent)
            ->line(Lang::get('Caso não tenha sido você, entre em contato imediatamente com o suporte.'));
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            //
        ];
    }
}
