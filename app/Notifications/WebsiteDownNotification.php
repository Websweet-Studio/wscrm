<?php

namespace App\Notifications;

use App\Models\WebsiteClient;
use Illuminate\Bus\Queueable;
use Illuminate\Notifications\Notification;

class WebsiteDownNotification extends Notification
{
    use Queueable;

    public function __construct(
        public WebsiteClient $website,
        public string $message,
        public int $httpCode = 0,
        public string $detail = '',
    ) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'website_id' => $this->website->id,
            'website_name' => $this->website->name,
            'url' => $this->website->url,
            'message' => $this->message,
            'http_code' => $this->httpCode,
            'detail' => $this->detail,
            'checked_at' => now()->toDateTimeString(),
        ];
    }
}
