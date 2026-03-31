<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Components;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final class ListRow implements ProvidesWhatsAppPayload
{
    private ?string $description = null;

    private function __construct(
        private readonly string $id,
        private readonly string $title,
    ) {}

    public static function create(string|int $id, string $title): self
    {
        return new self((string) $id, $title);
    }

    public function description(string $description): self
    {
        $clone = clone $this;
        $clone->description = $description;

        return $clone;
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        $payload = [
            'id' => $this->id,
            'title' => $this->title,
        ];

        if ($this->description !== null) {
            $payload['description'] = $this->description;
        }

        return $payload;
    }
}
