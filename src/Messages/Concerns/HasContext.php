<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Messages\Concerns;

trait HasContext
{
    protected ?string $contextMessageId = null;

    public function replyTo(string $messageId): static
    {
        return $this->contextMessageId($messageId);
    }

    public function contextMessageId(string $messageId): static
    {
        $clone = clone $this;
        $clone->contextMessageId = $messageId;

        return $clone;
    }

    /**
     * @return array<string, array<string, string>>
     */
    protected function buildContextPayload(): array
    {
        if ($this->contextMessageId === null) {
            return [];
        }

        return [
            'context' => [
                'message_id' => $this->contextMessageId,
            ],
        ];
    }
}
