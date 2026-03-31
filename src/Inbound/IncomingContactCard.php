<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Inbound;

final readonly class IncomingContactCard
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(private array $payload) {}

    public static function fromValue(mixed $payload): ?self
    {
        return is_array($payload) ? new self($payload) : null;
    }

    public function formattedName(): ?string
    {
        $name = $this->payload['name']['formatted_name'] ?? null;

        return is_string($name) ? $name : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
