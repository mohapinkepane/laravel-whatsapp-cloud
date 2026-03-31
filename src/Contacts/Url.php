<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Contacts;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class Url implements ProvidesWhatsAppPayload
{
    public function __construct(
        private string $url,
        private ?string $type = null,
    ) {}

    public static function create(string $url, ?string $type = null): self
    {
        return new self($url, $type);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'url' => $this->url,
            'type' => $this->type,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
