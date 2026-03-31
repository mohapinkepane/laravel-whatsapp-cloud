<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Flows;

use Mohapinkepane\WhatsAppCloud\Exceptions\FlowTokenException;
use Mohapinkepane\WhatsAppCloud\Flows\Contracts\ValidatesFlowToken;

final readonly class FlowTokenValidator implements ValidatesFlowToken
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function validate(array $payload, ?string $expectedToken = null): void
    {
        if ($expectedToken === null || $expectedToken === '') {
            return;
        }

        $receivedToken = $payload['flow_token'] ?? null;

        if (! is_string($receivedToken) || $receivedToken === '') {
            throw FlowTokenException::missingToken();
        }

        if (! hash_equals($expectedToken, $receivedToken)) {
            throw FlowTokenException::invalidToken();
        }
    }
}
