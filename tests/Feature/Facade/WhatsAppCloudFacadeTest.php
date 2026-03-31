<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Mohapinkepane\WhatsAppCloud\Facades\WhatsAppCloud;
use Mohapinkepane\WhatsAppCloud\Messages\TextMessage;

it('sends messages through the facade', function (): void {
    Http::fake([
        '*' => Http::response([
            'messages' => [
                ['id' => 'wamid.facade'],
            ],
        ], 200),
    ]);

    $response = WhatsAppCloud::sendMessage('26750000000', TextMessage::create('Hello facade'));

    expect($response->json('messages.0.id'))->toBe('wamid.facade');

    Http::assertSent(fn ($request): bool => $request['to'] === '26750000000'
        && $request['type'] === 'text');
});

it('supports fluent reply chaining before dispatching facade sends', function (): void {
    Http::fake([
        '*' => Http::response([
            'messages' => [
                ['id' => 'wamid.reply'],
            ],
        ], 200),
    ]);

    $response = WhatsAppCloud::sendMessage(
        '26750000000',
        TextMessage::create('Hello facade'),
    )->replyTo('wamid.origin');

    expect($response->json('messages.0.id'))->toBe('wamid.reply');

    Http::assertSent(fn ($request): bool => $request['to'] === '26750000000'
        && $request['type'] === 'text'
        && $request['context']['message_id'] === 'wamid.origin');
});

it('supports the replyTo alias on context-aware message builders', function (): void {
    Http::fake([
        '*' => Http::response([
            'messages' => [
                ['id' => 'wamid.reply.alias'],
            ],
        ], 200),
    ]);

    $response = WhatsAppCloud::sendMessage(
        '26750000000',
        TextMessage::create('Hello facade')->replyTo('wamid.origin'),
    );

    expect($response->json('messages.0.id'))->toBe('wamid.reply.alias');

    Http::assertSent(fn ($request): bool => $request['to'] === '26750000000'
        && $request['context']['message_id'] === 'wamid.origin');
});
