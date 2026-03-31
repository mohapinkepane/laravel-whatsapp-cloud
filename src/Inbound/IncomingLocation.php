<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Inbound;

final readonly class IncomingLocation
{
    public function __construct(
        private float $longitude,
        private float $latitude,
        private ?string $name,
        private ?string $address,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): ?self
    {
        $location = $payload['location'] ?? null;

        if (! is_array($location)) {
            return null;
        }

        $longitude = $location['longitude'] ?? null;
        $latitude = $location['latitude'] ?? null;

        if (! is_numeric($longitude) || ! is_numeric($latitude)) {
            return null;
        }

        return new self(
            (float) $longitude,
            (float) $latitude,
            is_string($location['name'] ?? null) ? $location['name'] : null,
            is_string($location['address'] ?? null) ? $location['address'] : null,
        );
    }

    public function longitude(): float
    {
        return $this->longitude;
    }

    public function latitude(): float
    {
        return $this->latitude;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    public function address(): ?string
    {
        return $this->address;
    }
}
