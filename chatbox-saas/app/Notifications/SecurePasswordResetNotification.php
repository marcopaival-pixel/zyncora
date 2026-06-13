<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Lang;

class SecurePasswordResetNotification extends Notification
{
    use Queueable;

    public $token;

    public $ipAddress;

    public $userAgent;

    /**
     * Create a new notification instance.
     */
    public function __construct($token, $ipAddress = null, $userAgent = null)
    {
        $this->token = $token;
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
        $url = route('filament.admin.auth.password-reset', ['token' => $this->token, 'email' => $notifiable->getEmailForPasswordReset()]);

        $expiration = config('auth.passwords.'.config('auth.defaults.passwords').'.expire');

        return (new MailMessage)
            ->subject(Lang::get('Recuperação de Senha Segura - Zynkora'))
            ->greeting('Olá, '.$notifiable->name.'!')
            ->line(Lang::get('Recebemos uma solicitação para redefinir a senha da sua conta.'))
            ->line(Lang::get('Detalhes da solicitação:'))
            ->line(Lang::get('- Data/Hora: ').now()->format('d/m/Y H:i:s'))
            ->line(Lang::get('- Endereço IP: ').$this->ipAddress)
            ->line(Lang::get('- Navegador/Dispositivo: ').$this->userAgent)
            ->action(Lang::get('Redefinir Senha'), $url)
            ->line(Lang::get('Este link de redefinição de senha expirará em :count minutos.', ['count' => $expiration]))
            ->line(Lang::get('Se você não solicitou a redefinição de senha, nenhuma ação adicional é necessária. Caso suspeite de alguma atividade incomum, entre em contato com o suporte imediatamente.'));
    }
}
