<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Mohapinkepane\WhatsAppCloud\Facades\WhatsAppCloud;

it('shows typing indicators through the facade', function (): void {
    Http::fake([
        '*' => Http::response(['success' => true], 200),
    ]);

    $response = WhatsAppCloud::showTypingIndicator('wamid.typing');

    expect($response->json('success'))->toBeTrue();

    Http::assertSent(fn ($request): bool => $request->url() === 'https://graph.facebook.com/v23.0/123456789/messages'
        && $request['status'] === 'read'
        && $request['message_id'] === 'wamid.typing'
        && $request['typing_indicator']['type'] === 'text');
});
