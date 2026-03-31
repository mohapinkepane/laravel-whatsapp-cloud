<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Webhooks;

use Mohapinkepane\WhatsAppCloud\Inbound\IncomingMessage;

final readonly class WebhookPayload
{
    /**
     * @param  array<int, IncomingMessage>  $messages
     * @param  array<int, WebhookStatus>  $statuses
     * @param  array<string, mixed>  $raw
     */
    public function __construct(
        private array $messages,
        private array $statuses,
        private array $raw,
    ) {}

    /**
     * @return array<int, IncomingMessage>
     */
    public function messages(): array
    {
        return $this->messages;
    }

    /**
     * @return array<int, WebhookStatus>
     */
    public function statuses(): array
    {
        return $this->statuses;
    }

    /**
     * @return array<string, mixed>
     */
    public function raw(): array
    {
        return $this->raw;
    }
}
