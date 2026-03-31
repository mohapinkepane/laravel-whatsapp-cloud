<?php

declare(strict_types=1);

use Mohapinkepane\WhatsAppCloud\Messages\MediaMessage;

it('serializes an image media message', function (): void {
    $payload = MediaMessage::create('image')
        ->url('https://example.com/image.jpg')
        ->caption('Example image')
        ->contextMessageId('wamid.media')
        ->toArray();

    expect($payload)->toBe([
        'context' => [
            'message_id' => 'wamid.media',
        ],
        'messaging_product' => 'whatsapp',
        'type' => 'image',
        'image' => [
            'link' => 'https://example.com/image.jpg',
            'caption' => 'Example image',
        ],
    ]);
});
