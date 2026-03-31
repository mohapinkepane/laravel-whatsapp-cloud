<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Messages;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Messages\Concerns\HasContext;

final class LocationRequestMessage implements ProvidesWhatsAppPayload
{
    use HasContext;

    private function __construct(private readonly string $body) {}

    public static function create(string $body): self
    {
        return new self($body);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge($this->buildContextPayload(), [
            'messaging_product' => 'whatsapp',
            'type' => 'interactive',
            'interactive' => [
                'type' => 'location_request_message',
                'body' => ['text' => $this->body],
                'action' => ['name' => 'send_location'],
            ],
        ]);
    }
}
