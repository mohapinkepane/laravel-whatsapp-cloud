<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Contracts;

use Illuminate\Notifications\Notification;
use Mohapinkepane\WhatsAppCloud\Support\Recipient;

interface ReceivesWhatsAppNotifications
{
    public function routeNotificationForWhatsApp(Notification $notification): Recipient|string|null;
}
