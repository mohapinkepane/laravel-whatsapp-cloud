<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Http;
use Mohapinkepane\WhatsAppCloud\Client\WhatsAppClient;

it('fetches conversational components', function (): void {
    Http::fake([
        '*' => Http::response(['enable_welcome_message' => true], 200),
    ]);

    $response = resolve(WhatsAppClient::class)->conversationalComponents();

    expect($response->json('enable_welcome_message'))->toBeTrue();
});

it('returns media download urls', function (): void {
    Http::fake([
        '*' => Http::response(['url' => 'https://lookaside.fbsbx.com/media-file'], 200),
    ]);

    $url = resolve(WhatsAppClient::class)->mediaUrl('media-123');

    expect($url)->toBe('https://lookaside.fbsbx.com/media-file');
});

it('marks messages as read through the status manager', function (): void {
    Http::fake([
        '*' => Http::response(['success' => true], 200),
    ]);

    $response = resolve(WhatsAppClient::class)->markMessageAsRead('wamid.123');

    expect($response->json('success'))->toBeTrue();

    Http::assertSent(fn ($request): bool => $request->url() === 'https://graph.facebook.com/v23.0/123456789/messages'
        && $request['status'] === 'read'
        && $request['message_id'] === 'wamid.123');
});

it('shows typing indicators through the status manager', function (): void {
    Http::fake([
        '*' => Http::response(['success' => true], 200),
    ]);

    $response = resolve(WhatsAppClient::class)->showTypingIndicator('wamid.123');

    expect($response->json('success'))->toBeTrue();

    Http::assertSent(fn ($request): bool => $request->url() === 'https://graph.facebook.com/v23.0/123456789/messages'
        && $request['status'] === 'read'
        && $request['message_id'] === 'wamid.123'
        && $request['typing_indicator']['type'] === 'text');
});

it('supports the typingIndicator alias', function (): void {
    Http::fake([
        '*' => Http::response(['success' => true], 200),
    ]);

    $response = resolve(WhatsAppClient::class)->typingIndicator('wamid.123');

    expect($response->json('success'))->toBeTrue();

    Http::assertSent(fn ($request): bool => $request['typing_indicator']['type'] === 'text');
});

it('uploads media files', function (): void {
    Http::fake([
        '*' => Http::response(['id' => 'media-uploaded'], 200),
    ]);

    $file = tempnam(sys_get_temp_dir(), 'wa-media-');
    file_put_contents($file, 'dummy-media-content');

    $response = resolve(WhatsAppClient::class)->uploadMedia($file, 'application/pdf', 'brochure.pdf');

    expect($response->json('id'))->toBe('media-uploaded');

    Http::assertSent(fn ($request): bool => $request->url() === 'https://graph.facebook.com/v23.0/123456789/media');

    @unlink($file);
});
