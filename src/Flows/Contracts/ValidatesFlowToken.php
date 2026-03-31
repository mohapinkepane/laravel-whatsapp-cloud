<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Flows\Contracts;

interface ValidatesFlowToken
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function validate(array $payload, ?string $expectedToken = null): void;
}
