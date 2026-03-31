<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Messages;

use Mohapinkepane\WhatsAppCloud\Components\InteractiveHeader;
use Mohapinkepane\WhatsAppCloud\Components\ProductSection;
use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Exceptions\ValidationException;
use Mohapinkepane\WhatsAppCloud\Messages\Concerns\HasContext;

final class ProductListMessage implements ProvidesWhatsAppPayload
{
    use HasContext;

    private ?InteractiveHeader $header = null;

    private ?string $footer = null;

    /**
     * @var array<int, ProductSection>
     */
    private array $sections = [];

    private function __construct(
        private readonly string $body,
        private readonly string $catalogId,
    ) {}

    public static function create(string $body, string $catalogId): self
    {
        return new self($body, $catalogId);
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

    public function addSection(ProductSection $section): self
    {
        $clone = clone $this;
        $clone->sections[] = $section;

        return $clone;
    }

    /**
     * @param  array<int, ProductSection>  $sections
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
            throw ValidationException::invalidMessage('Interactive product lists require at least one section.');
        }

        return array_merge($this->buildContextPayload(), [
            'messaging_product' => 'whatsapp',
            'type' => 'interactive',
            'interactive' => array_filter([
                'type' => 'product_list',
                'header' => $this->header?->toArray(),
                'body' => ['text' => $this->body],
                'footer' => $this->footer !== null ? ['text' => $this->footer] : null,
                'action' => [
                    'catalog_id' => $this->catalogId,
                    'sections' => array_map(
                        static fn (ProductSection $section): array => $section->toArray(),
                        $this->sections,
                    ),
                ],
            ], static fn (mixed $value): bool => $value !== null),
        ]);
    }
}
