<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Contacts;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class Phone implements ProvidesWhatsAppPayload
{
    public function __construct(
        private string $phone,
        private ?string $type = null,
        private ?string $waId = null,
    ) {}

    public static function create(string $phone, ?string $type = null, ?string $waId = null): self
    {
        return new self($phone, $type, $waId);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'phone' => $this->phone,
            'type' => $this->type,
            'wa_id' => $this->waId,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
