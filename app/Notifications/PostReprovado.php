<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class PostReprovado extends Notification
{
    use Queueable;

    public $post;
    public $motivo;

    public function __construct($post, $motivo)
    {
        $this->post = $post;
        $this->motivo = $motivo;
    }

    public function via($notifiable)
    {
        // Envia para banco e e-mail
        return ['database'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Seu post foi reprovado')
            ->greeting('Olá ' . $notifiable->name)
            ->line('Seu post "' . $this->post->titulo . '" foi reprovado.')
            ->line('Motivo: ' . $this->motivo)
            ->action('Revisar Post', url('/posts/' . $this->post->id . '/editar'))
            ->line('Corrija e envie novamente para análise.');
    }

    public function toDatabase($notifiable)
    {
        return [
            'post_id' => $this->post->id,
            //'titulo' => $this->post->titulo,
            'motivo' => $this->motivo,
            //'url' => url('/posts/' . $this->post->id . '/editar')
        ];
    }
}
