<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Inbound;

final readonly class IncomingOrder
{
    /**
     * @param  array<int, IncomingOrderItem>  $items
     */
    public function __construct(
        private string $catalogId,
        private ?string $text,
        private array $items,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): ?self
    {
        $order = $payload['order'] ?? null;

        if (! is_array($order) || ! is_string($order['catalog_id'] ?? null)) {
            return null;
        }

        $items = array_values(array_filter(array_map(
            static fn (mixed $item): ?IncomingOrderItem => is_array($item) ? IncomingOrderItem::fromArray($item) : null,
            is_array($order['product_items'] ?? null) ? $order['product_items'] : [],
        )));

        return new self(
            $order['catalog_id'],
            is_string($order['text'] ?? null) ? $order['text'] : null,
            $items,
        );
    }

    public function catalogId(): string
    {
        return $this->catalogId;
    }

    public function text(): ?string
    {
        return $this->text;
    }

    /**
     * @return array<int, IncomingOrderItem>
     */
    public function items(): array
    {
        return $this->items;
    }
}
