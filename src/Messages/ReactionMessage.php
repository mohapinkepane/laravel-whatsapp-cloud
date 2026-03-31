<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Messages;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class ReactionMessage implements ProvidesWhatsAppPayload
{
    private function __construct(
        private string $messageId,
        private string $emoji,
    ) {}

    public static function create(string $messageId, string $emoji): self
    {
        return new self($messageId, $emoji);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'messaging_product' => 'whatsapp',
            'type' => 'reaction',
            'reaction' => [
                'message_id' => $this->messageId,
                'emoji' => $this->emoji,
            ],
        ];
    }
}
