<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Contacts;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class Name implements ProvidesWhatsAppPayload
{
    public function __construct(
        private string $firstName,
        private string $formattedName,
        private ?string $lastName = null,
        private ?string $middleName = null,
        private ?string $prefix = null,
        private ?string $suffix = null,
    ) {}

    public static function create(
        string $firstName,
        string $formattedName,
        ?string $lastName = null,
        ?string $middleName = null,
        ?string $prefix = null,
        ?string $suffix = null,
    ): self {
        return new self($firstName, $formattedName, $lastName, $middleName, $prefix, $suffix);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'first_name' => $this->firstName,
            'formatted_name' => $this->formattedName,
            'last_name' => $this->lastName,
            'middle_name' => $this->middleName,
            'prefix' => $this->prefix,
            'suffix' => $this->suffix,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
