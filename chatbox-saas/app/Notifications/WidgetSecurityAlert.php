<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class WidgetSecurityAlert extends Notification implements ShouldQueue
{
    use Queueable;

    public string $type;
    public string $title;
    public string $message;
    public array $data;

    /**
     * Create a new notification instance.
     */
    public function __construct(string $type, string $title, string $message, array $data = [])
    {
        $this->type = $type;
        $this->title = $title;
        $this->message = $message;
        $this->data = $data;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database', 'mail'];
    }

    /**
     * Get the mail representation of the notification.
     */
    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
                    ->error() // Red button/header for security alerts
                    ->subject('Alerta de Segurança - Widget Chatbot')
                    ->greeting("Olá, $notifiable->name")
                    ->line("**{$this->title}**")
                    ->line($this->message)
                    ->action('Ver Painel de Segurança', url('/admin/security'))
                    ->line('Por favor, revise as configurações do seu chatbot imediatamente.');
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => $this->type,
            'title' => $this->title,
            'message' => $this->message,
            'data' => $this->data,
        ];
    }
}
