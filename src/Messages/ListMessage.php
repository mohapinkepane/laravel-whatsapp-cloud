<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Messages;

use Mohapinkepane\WhatsAppCloud\Components\InteractiveHeader;
use Mohapinkepane\WhatsAppCloud\Components\ListSection;
use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Exceptions\ValidationException;
use Mohapinkepane\WhatsAppCloud\Messages\Concerns\HasContext;

final class ListMessage implements ProvidesWhatsAppPayload
{
    use HasContext;

    private ?InteractiveHeader $header = null;

    private ?string $footer = null;

    /**
     * @var array<int, ListSection>
     */
    private array $sections = [];

    private function __construct(
        private readonly string $body,
        private readonly string $buttonText,
    ) {}

    public static function create(string $body, string $buttonText): self
    {
        return new self($body, $buttonText);
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

    public function addSection(ListSection $section): self
    {
        $clone = clone $this;
        $clone->sections[] = $section;

        return $clone;
    }

    /**
     * @param  array<int, ListSection>  $sections
     */
    public function addSections(array $sections): self
    {
        $clone = clone $this;
        $clone->sections = [...$clone->sections, ...$sections];

        return $clone;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        if ($this->sections === []) {
            throw ValidationException::invalidMessage('Interactive lists require at least one section.');
        }

        return array_merge($this->buildContextPayload(), [
            'messaging_product' => 'whatsapp',
            'type' => 'interactive',
            'interactive' => array_filter([
                'type' => 'list',
                'header' => $this->header?->toArray(),
                'body' => ['text' => $this->body],
                'footer' => $this->footer !== null ? ['text' => $this->footer] : null,
                'action' => [
                    'button' => $this->buttonText,
                    'sections' => array_map(
                        static fn (ListSection $section): array => $section->toArray(),
                        $this->sections,
                    ),
                ],
            ], static fn (mixed $value): bool => $value !== null),
        ]);
    }
}
