<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Components;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class ReplyButton implements ProvidesWhatsAppPayload
{
    private function __construct(
        private string $id,
        private string $title,
    ) {}

    public static function create(string|int $id, string $title): self
    {
        return new self((string) $id, $title);
    }

    /**
     * @return array{type: string, reply: array{id: string, title: string}}
     */
    public function toArray(): array
    {
        return [
            'type' => 'reply',
            'reply' => [
                'id' => $this->id,
                'title' => $this->title,
            ],
        ];
    }
}
