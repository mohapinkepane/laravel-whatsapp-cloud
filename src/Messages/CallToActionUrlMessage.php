<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Messages;

use Mohapinkepane\WhatsAppCloud\Components\InteractiveHeader;
use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Messages\Concerns\HasContext;

final class CallToActionUrlMessage implements ProvidesWhatsAppPayload
{
    use HasContext;

    private ?InteractiveHeader $header = null;

    private ?string $footer = null;

    private function __construct(
        private readonly string $body,
        private readonly string $buttonText,
        private readonly string $url,
    ) {}

    public static function create(string $body, string $buttonText, string $url): self
    {
        return new self($body, $buttonText, $url);
    }

    public function addHeader(InteractiveHeader $header): self
    {
        $clone = clone $this;
        $clone->header = $header;

        return $clone;
    }

    public function addFooter(string $footer): self
    {
        $clone = clone $this;
        $clone->footer = $footer;

        return $clone;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge($this->buildContextPayload(), [
            'messaging_product' => 'whatsapp',
            'type' => 'interactive',
            'interactive' => array_filter([
                'type' => 'cta_url',
                'header' => $this->header?->toArray(),
                'body' => ['text' => $this->body],
                'footer' => $this->footer !== null ? ['text' => $this->footer] : null,
                'action' => [
                    'name' => 'cta_url',
                    'parameters' => [
                        'display_text' => $this->buttonText,
                        'url' => $this->url,
                    ],
                ],
            ], static fn (mixed $value): bool => $value !== null),
        ]);
    }
}
