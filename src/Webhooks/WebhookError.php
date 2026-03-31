<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Webhooks;

final readonly class WebhookError
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private ?int $code,
        private ?string $title,
        private ?string $message,
        private array $payload,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): self
    {
        $code = $payload['code'] ?? null;

        return new self(
            is_int($code) ? $code : null,
            is_string($payload['title'] ?? null) ? $payload['title'] : null,
            is_string($payload['message'] ?? null) ? $payload['message'] : null,
            $payload,
        );
    }

    public function code(): ?int
    {
        return $this->code;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function message(): ?string
    {
        return $this->message;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
