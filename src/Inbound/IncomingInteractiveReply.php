<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Inbound;

final readonly class IncomingInteractiveReply
{
    /**
     * @param  array<string, mixed>|null  $flowResponse
     */
    public function __construct(
        private string $type,
        private ?string $id,
        private ?string $title,
        private ?string $description,
        private ?array $flowResponse,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): ?self
    {
        $interactive = $payload['interactive'] ?? null;

        if (! is_array($interactive) || ! is_string($interactive['type'] ?? null)) {
            return null;
        }

        $type = $interactive['type'];
        $flowResponse = is_array($interactive['nfm_reply']['response_json'] ?? null) ? $interactive['nfm_reply']['response_json'] : null;

        return match ($type) {
            'button_reply' => new self(
                $type,
                is_string($interactive['button_reply']['id'] ?? null) ? $interactive['button_reply']['id'] : null,
                is_string($interactive['button_reply']['title'] ?? null) ? $interactive['button_reply']['title'] : null,
                null,
                $flowResponse,
            ),
            'list_reply' => new self(
                $type,
                is_string($interactive['list_reply']['id'] ?? null) ? $interactive['list_reply']['id'] : null,
                is_string($interactive['list_reply']['title'] ?? null) ? $interactive['list_reply']['title'] : null,
                is_string($interactive['list_reply']['description'] ?? null) ? $interactive['list_reply']['description'] : null,
                $flowResponse,
            ),
            'nfm_reply' => new self(
                $type,
                null,
                null,
                null,
                $flowResponse,
            ),
            default => null,
        };
    }

    public function type(): string
    {
        return $this->type;
    }

    public function id(): ?string
    {
        return $this->id;
    }

    public function title(): ?string
    {
        return $this->title;
    }

    public function description(): ?string
    {
        return $this->description;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function flowResponse(): ?array
    {
        return $this->flowResponse;
    }
}
