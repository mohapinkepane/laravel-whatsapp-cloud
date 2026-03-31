<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Notifications;

use Illuminate\Http\Client\Response;
use Illuminate\Notifications\Notification;
use Mohapinkepane\WhatsAppCloud\Client\WhatsAppClient;
use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Exceptions\ValidationException;
use Mohapinkepane\WhatsAppCloud\Support\Recipient;

final readonly class WhatsAppChannel
{
    public function __construct(private WhatsAppClient $client) {}

    public function send(mixed $notifiable, Notification $notification): ?Response
    {
        if (! method_exists($notification, 'toWhatsApp')) {
            return null;
        }

        $message = $notification->toWhatsApp($notifiable);

        if ($message === null) {
            return null;
        }

        if (is_array($message)) {
            $message = WhatsAppMessage::raw($message);
        }

        if ($message instanceof ProvidesWhatsAppPayload) {
            $message = WhatsAppMessage::using($message);
        }

        if (! $message instanceof WhatsAppMessage) {
            throw ValidationException::invalidMessage('Notifications must return a WhatsAppMessage or a payload builder from toWhatsApp().');
        }

        $recipient = $message->recipient() ?? $this->resolveRecipient($notifiable, $notification);

        if ($recipient === null) {
            return null;
        }

        return $this->client->sendMessage($recipient, $message->payload(), $message->resolvedPhoneNumberId());
    }

    private function resolveRecipient(mixed $notifiable, Notification $notification): Recipient|string|null
    {
        if (! method_exists($notifiable, 'routeNotificationFor')) {
            return null;
        }

        /** @var Recipient|string|null $route */
        $route = $notifiable->routeNotificationFor('whatsapp', $notification);

        return $route;
    }
}
