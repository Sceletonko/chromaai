<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class VerifyEmailWithCode extends Notification
{
    use Queueable;

    protected $code;

    public function __construct($code)
    {
        $this->code = $code;
    }

    public function via($notifiable)
    {
        return ['mail'];
    }

    public function toMail($notifiable)
    {
        return (new MailMessage)
            ->subject('Overenie vášho emailu - ChromaAi')
            ->line('Váš overovací kód pre registráciu v ChromaAi je:')
            ->line('**' . $this->code . '**')
            ->line('Tento kód zadajte na stránke overenia pre dokončenie registrácie.')
            ->line('Ak ste si nevytvorili účet, túto správu môžete ignorovať.');
    }
}
