<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Messages;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Exceptions\ValidationException;
use Mohapinkepane\WhatsAppCloud\Messages\Concerns\HasContext;

final class TextMessage implements ProvidesWhatsAppPayload
{
    use HasContext;

    private bool $previewUrl = false;

    private function __construct(private readonly string $body)
    {
        if ($body === '') {
            throw ValidationException::invalidMessage('Text messages require a non-empty body.');
        }
    }

    public static function create(string $body): self
    {
        return new self($body);
    }

    public function previewUrl(bool $enabled = true): self
    {
        $clone = clone $this;
        $clone->previewUrl = $enabled;

        return $clone;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge($this->buildContextPayload(), [
            'messaging_product' => 'whatsapp',
            'type' => 'text',
            'text' => [
                'body' => $this->body,
                'preview_url' => $this->previewUrl,
            ],
        ]);
    }
}
