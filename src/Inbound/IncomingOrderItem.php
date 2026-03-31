<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Inbound;

final readonly class IncomingOrderItem
{
    public function __construct(
        private string $productRetailerId,
        private ?int $quantity,
        private ?string $itemPrice,
        private ?string $currency,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): ?self
    {
        $productRetailerId = $payload['product_retailer_id'] ?? null;

        if (! is_string($productRetailerId) || $productRetailerId === '') {
            return null;
        }

        return new self(
            $productRetailerId,
            is_int($payload['quantity'] ?? null) ? $payload['quantity'] : null,
            is_string($payload['item_price'] ?? null) ? $payload['item_price'] : null,
            is_string($payload['currency'] ?? null) ? $payload['currency'] : null,
        );
    }

    public function productRetailerId(): string
    {
        return $this->productRetailerId;
    }

    public function quantity(): ?int
    {
        return $this->quantity;
    }

    public function itemPrice(): ?string
    {
        return $this->itemPrice;
    }

    public function currency(): ?string
    {
        return $this->currency;
    }
}
