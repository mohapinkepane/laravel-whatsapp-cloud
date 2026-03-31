<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Inbound;

final readonly class IncomingReaction
{
    public function __construct(
        private string $messageId,
        private string $emoji,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): ?self
    {
        $reaction = $payload['reaction'] ?? null;

        if (! is_array($reaction)) {
            return null;
        }

        $messageId = $reaction['message_id'] ?? null;
        $emoji = $reaction['emoji'] ?? null;

        if (! is_string($messageId) || ! is_string($emoji)) {
            return null;
        }

        return new self($messageId, $emoji);
    }

    public function messageId(): string
    {
        return $this->messageId;
    }

    public function emoji(): string
    {
        return $this->emoji;
    }
}
