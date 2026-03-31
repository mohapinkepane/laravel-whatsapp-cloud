<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Components;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class ProductItem implements ProvidesWhatsAppPayload
{
    public function __construct(private string $productRetailerId) {}

    public static function create(string $productRetailerId): self
    {
        return new self($productRetailerId);
    }

    /**
     * @return array{product_retailer_id: string}
     */
    public function toArray(): array
    {
        return [
            'product_retailer_id' => $this->productRetailerId,
        ];
    }
}
