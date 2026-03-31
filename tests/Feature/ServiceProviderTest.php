<?php

declare(strict_types=1);

use Mohapinkepane\WhatsAppCloud\Client\WhatsAppClient;
use Mohapinkepane\WhatsAppCloud\Config\WhatsAppConfig;

it('registers the package services', function (): void {
    $config = resolve(WhatsAppConfig::class);

    expect($config->graphApiVersion())->toBe('v23.0')
        ->and(resolve(WhatsAppClient::class))->toBeInstanceOf(WhatsAppClient::class);
});
