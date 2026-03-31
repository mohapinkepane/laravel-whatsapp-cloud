<?php

declare(strict_types=1);

use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Http;

it('generates a whatsapp key pair', function (): void {
    $exitCode = Artisan::call('whatsapp:generate-key-pair', ['passphrase' => 'test-passphrase']);
    $output = Artisan::output();

    expect($exitCode)->toBe(0)
        ->and($output)->toContain('WHATSAPP_KEYS_PASSPHRASE="test-passphrase"')
        ->and($output)->toContain('WHATSAPP_PUBLIC_KEY=')
        ->and($output)->toContain('WHATSAPP_PRIVATE_KEY=');
});

it('publishes the whatsapp public key', function (): void {
    Http::fake([
        '*' => Http::response(['success' => true], 200),
    ]);

    config()->set('whatsapp-cloud.flow.public_key', '-----BEGIN PUBLIC KEY-----test-----END PUBLIC KEY-----');

    $exitCode = Artisan::call('whatsapp:publish-public-key');

    expect($exitCode)->toBe(0)
        ->and(Artisan::output())->toContain('WhatsApp public key published successfully.');

    Http::assertSent(fn ($request): bool => $request->url() === 'https://graph.facebook.com/v23.0/123456789/whatsapp_business_encryption'
        && $request['business_public_key'] === '-----BEGIN PUBLIC KEY-----test-----END PUBLIC KEY-----');
});

it('syncs conversational components', function (): void {
    Http::fake([
        '*' => Http::response(['success' => true], 200),
    ]);

    $exitCode = Artisan::call('whatsapp:sync-conversational-components');

    expect($exitCode)->toBe(0)
        ->and(Artisan::output())->toContain('Conversational components synced successfully.');

    Http::assertSent(fn ($request): bool => $request->url() === 'https://graph.facebook.com/v23.0/123456789/conversational_automation'
        && $request['enable_welcome_message'] === true
        && $request['commands'][0]['command_name'] === 'help');
});
