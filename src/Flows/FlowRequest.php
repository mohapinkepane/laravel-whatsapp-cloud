<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Flows;

final readonly class FlowRequest
{
    /**
     * @param  array<string, mixed>  $body
     */
    public function __construct(
        private array $body,
        private string $aesKey,
        private string $initialVector,
    ) {}

    /**
     * @return array<string, mixed>
     */
    public function body(): array
    {
        return $this->body;
    }

    public function aesKey(): string
    {
        return $this->aesKey;
    }

    public function initialVector(): string
    {
        return $this->initialVector;
    }
}
