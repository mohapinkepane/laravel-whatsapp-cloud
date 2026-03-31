<?php

declare(strict_types=1);

use Mohapinkepane\WhatsAppCloud\Components\InteractiveHeader;
use Mohapinkepane\WhatsAppCloud\Components\ListRow;
use Mohapinkepane\WhatsAppCloud\Components\ListSection;
use Mohapinkepane\WhatsAppCloud\Components\ReplyButton;
use Mohapinkepane\WhatsAppCloud\Messages\ListMessage;
use Mohapinkepane\WhatsAppCloud\Messages\LocationRequestMessage;
use Mohapinkepane\WhatsAppCloud\Messages\ReplyButtonsMessage;

it('serializes interactive reply buttons', function (): void {
    $payload = ReplyButtonsMessage::create('Choose one')
        ->addHeader(InteractiveHeader::text('Welcome'))
        ->addFooter('Powered by tests')
        ->addButtons([
            ReplyButton::create(1, 'First'),
            ReplyButton::create(2, 'Second'),
        ])
        ->toArray();

    expect($payload['interactive']['type'])->toBe('button')
        ->and($payload['interactive']['action']['buttons'])->toHaveCount(2)
        ->and($payload['interactive']['header']['text'])->toBe('Welcome');
});

it('serializes interactive lists', function (): void {
    $payload = ListMessage::create('Pick an option', 'View')
        ->addSection(ListSection::create('Main', [
            ListRow::create('1', 'Flights')->description('Book a flight'),
            ListRow::create('2', 'Hotels'),
        ]))
        ->toArray();

    expect($payload['interactive']['type'])->toBe('list')
        ->and($payload['interactive']['action']['sections'][0]['rows'])->toHaveCount(2);
});

it('serializes a location request message', function (): void {
    $payload = LocationRequestMessage::create('Share your location')->toArray();

    expect($payload)->toBe([
        'messaging_product' => 'whatsapp',
        'type' => 'interactive',
        'interactive' => [
            'type' => 'location_request_message',
            'body' => ['text' => 'Share your location'],
            'action' => ['name' => 'send_location'],
        ],
    ]);
});
