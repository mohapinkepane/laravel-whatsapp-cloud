<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Webhooks;

use Illuminate\Http\Request;
use Mohapinkepane\WhatsAppCloud\Config\WhatsAppConfig;

final readonly class WebhookSignatureValidator
{
    public function __construct(private WhatsAppConfig $config) {}

    public function isValid(Request $request): bool
    {
        $secret = $this->config->webhookAppSecret();

        if ($secret === null) {
            return true;
        }

        $signature = $request->header('X-Hub-Signature-256');

        if (! is_string($signature) || ! str_starts_with($signature, 'sha256=')) {
            return false;
        }

        $expected = 'sha256='.hash_hmac('sha256', $request->getContent(), $secret);

        return hash_equals($expected, $signature);
    }
}
