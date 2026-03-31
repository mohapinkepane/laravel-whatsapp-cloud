<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Components;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class ProductSection implements ProvidesWhatsAppPayload
{
    /**
     * @param  array<int, ProductItem>  $productItems
     */
    public function __construct(
        private string $title,
        private array $productItems,
    ) {}

    /**
     * @param  array<int, ProductItem>  $productItems
     */
    public static function create(string $title, array $productItems): self
    {
        return new self($title, $productItems);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'product_items' => array_map(
                static fn (ProductItem $item): array => $item->toArray(),
                $this->productItems,
            ),
        ];
    }
}
