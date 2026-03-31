<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Contacts;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class Email implements ProvidesWhatsAppPayload
{
    public function __construct(
        private string $email,
        private ?string $type = null,
    ) {}

    public static function create(string $email, ?string $type = null): self
    {
        return new self($email, $type);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'email' => $this->email,
            'type' => $this->type,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
