<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Inbound;

final readonly class IncomingSystemMessage
{
    public function __construct(
        private ?string $body,
        private ?string $identity,
        private ?string $newWaId,
        private ?string $type,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): ?self
    {
        $system = $payload['system'] ?? null;

        if (! is_array($system)) {
            return null;
        }

        return new self(
            is_string($system['body'] ?? null) ? $system['body'] : null,
            is_string($system['identity'] ?? null) ? $system['identity'] : null,
            is_string($system['new_wa_id'] ?? null) ? $system['new_wa_id'] : null,
            is_string($system['type'] ?? null) ? $system['type'] : null,
        );
    }

    public function body(): ?string
    {
        return $this->body;
    }

    public function identity(): ?string
    {
        return $this->identity;
    }

    public function newWaId(): ?string
    {
        return $this->newWaId;
    }

    public function type(): ?string
    {
        return $this->type;
    }
}
