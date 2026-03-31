<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Contacts;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class Address implements ProvidesWhatsAppPayload
{
    public function __construct(
        private string $city,
        private string $country,
        private ?string $countryCode = null,
        private ?string $state = null,
        private ?string $street = null,
        private ?string $type = null,
        private ?string $zip = null,
    ) {}

    public static function create(
        string $city,
        string $country,
        ?string $countryCode = null,
        ?string $state = null,
        ?string $street = null,
        ?string $type = null,
        ?string $zip = null,
    ): self {
        return new self($city, $country, $countryCode, $state, $street, $type, $zip);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'city' => $this->city,
            'country' => $this->country,
            'country_code' => $this->countryCode,
            'state' => $this->state,
            'street' => $this->street,
            'type' => $this->type,
            'zip' => $this->zip,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
