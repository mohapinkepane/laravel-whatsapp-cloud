<?php

declare(strict_types=1);

use Illuminate\Notifications\Notifiable;
use Illuminate\Notifications\Notification;
use Illuminate\Support\Facades\Http;
use Mohapinkepane\WhatsAppCloud\Messages\TextMessage;
use Mohapinkepane\WhatsAppCloud\Notifications\WhatsAppChannel;
use Mohapinkepane\WhatsAppCloud\Notifications\WhatsAppMessage;

it('sends notifications to a phone number route', function (): void {
    Http::fake([
        '*' => Http::response([
            'messages' => [
                ['id' => 'wamid.sent'],
            ],
        ], 200),
    ]);

    $notifiable = new class
    {
        use Notifiable;

        public function routeNotificationForWhatsApp(Notification $notification): string
        {
            return '26750000000';
        }
    };

    $notification = new class extends Notification
    {
        /**
         * @return array<int, class-string>
         */
        public function via(object $notifiable): array
        {
            return [WhatsAppChannel::class];
        }

        public function toWhatsApp(object $notifiable): WhatsAppMessage
        {
            return WhatsAppMessage::using(
                TextMessage::create('Hello from notification')->contextMessageId('wamid.origin')
            );
        }
    };

    $notifiable->notify($notification);

    Http::assertSent(fn ($request): bool => $request->url() === 'https://graph.facebook.com/v23.0/123456789/messages'
        && $request['to'] === '26750000000'
        && $request['type'] === 'text'
        && $request['context']['message_id'] === 'wamid.origin');
});

it('supports business scoped recipients on notifications', function (): void {
    Http::fake([
        '*' => Http::response([
            'messages' => [
                ['id' => 'wamid.sent'],
            ],
        ], 200),
    ]);

    $notifiable = new class
    {
        use Notifiable;
    };

    $notification = new class extends Notification
    {
        /**
         * @return array<int, class-string>
         */
        public function via(object $notifiable): array
        {
            return [WhatsAppChannel::class];
        }

        public function toWhatsApp(object $notifiable): WhatsAppMessage
        {
            return WhatsAppMessage::text('Hello BSUID')->toBusinessScopedUser('bsuid-123');
        }
    };

    $notifiable->notify($notification);

    Http::assertSent(fn ($request): bool => $request->url() === 'https://graph.facebook.com/v23.0/123456789/messages'
        && $request['recipient'] === 'bsuid-123'
        && ! isset($request['to']));
});
