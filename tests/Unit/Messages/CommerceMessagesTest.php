<?php

declare(strict_types=1);

use Mohapinkepane\WhatsAppCloud\Components\ProductItem;
use Mohapinkepane\WhatsAppCloud\Components\ProductSection;
use Mohapinkepane\WhatsAppCloud\Messages\ProductListMessage;
use Mohapinkepane\WhatsAppCloud\Messages\ProductMessage;

it('serializes a single product interactive message', function (): void {
    $payload = ProductMessage::create('Featured item', 'catalog-1', 'sku-123')->toArray();

    expect($payload)->toBe([
        'messaging_product' => 'whatsapp',
        'type' => 'interactive',
        'interactive' => [
            'type' => 'product',
            'body' => ['text' => 'Featured item'],
            'action' => [
                'catalog_id' => 'catalog-1',
                'product_retailer_id' => 'sku-123',
            ],
        ],
    ]);
});

it('serializes a product list interactive message', function (): void {
    $payload = ProductListMessage::create('Browse items', 'catalog-1')
        ->addSection(ProductSection::create('Popular', [
            ProductItem::create('sku-123'),
            ProductItem::create('sku-456'),
        ]))
        ->toArray();

    expect($payload['interactive']['type'])->toBe('product_list')
        ->and($payload['interactive']['action']['catalog_id'])->toBe('catalog-1')
        ->and($payload['interactive']['action']['sections'][0]['product_items'])->toHaveCount(2);
});
