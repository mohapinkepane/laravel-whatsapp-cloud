<?php

declare(strict_types=1);

use Mohapinkepane\WhatsAppCloud\Messages\TextMessage;

it('serializes a text message payload', function (): void {
    $payload = TextMessage::create('Hello from Laravel')
        ->previewUrl()
        ->contextMessageId('wamid.123')
        ->toArray();

    expect($payload)->toBe([
        'context' => [
            'message_id' => 'wamid.123',
        ],
        'messaging_product' => 'whatsapp',
        'type' => 'text',
        'text' => [
            'body' => 'Hello from Laravel',
            'preview_url' => true,
        ],
    ]);
});
