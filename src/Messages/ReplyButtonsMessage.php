<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Messages;

use Mohapinkepane\WhatsAppCloud\Components\InteractiveHeader;
use Mohapinkepane\WhatsAppCloud\Components\ReplyButton;
use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Exceptions\ValidationException;
use Mohapinkepane\WhatsAppCloud\Messages\Concerns\HasContext;

final class ReplyButtonsMessage implements ProvidesWhatsAppPayload
{
    use HasContext;

    private ?InteractiveHeader $header = null;

    private ?string $footer = null;

    /**
     * @var array<int, ReplyButton>
     */
    private array $buttons = [];

    private function __construct(private readonly string $body) {}

    public static function create(string $body): self
    {
        return new self($body);
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
     * @param  array<int, ReplyButton>  $buttons
     */
    public function addButtons(array $buttons): self
    {
        $clone = clone $this;
        $clone->buttons = [...$clone->buttons, ...$buttons];

        return $clone;
    }

    public function addButton(ReplyButton $button): self
    {
        return $this->addButtons([$button]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        if ($this->buttons === []) {
            throw ValidationException::invalidMessage('Interactive reply buttons require at least one button.');
        }

        return array_merge($this->buildContextPayload(), [
            'messaging_product' => 'whatsapp',
            'type' => 'interactive',
            'interactive' => array_filter([
                'type' => 'button',
                'header' => $this->header?->toArray(),
                'body' => ['text' => $this->body],
                'footer' => $this->footer !== null ? ['text' => $this->footer] : null,
                'action' => [
                    'buttons' => array_map(
                        static fn (ReplyButton $button): array => $button->toArray(),
                        $this->buttons,
                    ),
                ],
            ], static fn (mixed $value): bool => $value !== null),
        ]);
    }
}
