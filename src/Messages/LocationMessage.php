<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Messages;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Messages\Concerns\HasContext;

final class LocationMessage implements ProvidesWhatsAppPayload
{
    use HasContext;

    private function __construct(
        private readonly float $longitude,
        private readonly float $latitude,
        private readonly ?string $name,
        private readonly ?string $address,
    ) {}

    public static function create(float $longitude, float $latitude, ?string $name = null, ?string $address = null): self
    {
        return new self($longitude, $latitude, $name, $address);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge($this->buildContextPayload(), [
            'messaging_product' => 'whatsapp',
            'type' => 'location',
            'location' => array_filter([
                'longitude' => $this->longitude,
                'latitude' => $this->latitude,
                'name' => $this->name,
                'address' => $this->address,
            ], static fn (mixed $value): bool => $value !== null),
        ]);
    }
}
