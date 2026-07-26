<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class FinancialReminder extends Notification
{
    use Queueable;

    public function __construct(
        private readonly string $fingerprint,
        private readonly string $title,
        private readonly string $message,
        private readonly string $url,
        private readonly string $dueDate,
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'fingerprint' => $this->fingerprint,
            'title' => $this->title,
            'message' => $this->message,
            'url' => $this->url,
            'due_date' => $this->dueDate,
        ];
    }
}
