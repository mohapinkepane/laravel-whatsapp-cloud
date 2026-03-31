<?php

declare(strict_types=1);

use Mohapinkepane\WhatsAppCloud\Inbound\IncomingMessage;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingUser;

it('exposes typed location, order, and system inbound dto helpers', function (): void {
    $message = new IncomingMessage(
        'wamid.2',
        'order',
        new IncomingUser('user-2', null, null, '26750000000', '26750000000', 'Neo'),
        [
            'location' => [
                'longitude' => 27.5,
                'latitude' => -22.1,
                'name' => 'Store',
                'address' => 'Main street',
            ],
            'order' => [
                'catalog_id' => 'catalog-1',
                'text' => 'Order request',
                'product_items' => [[
                    'product_retailer_id' => 'sku-123',
                    'quantity' => 2,
                    'item_price' => '10.00',
                    'currency' => 'USD',
                ]],
            ],
            'system' => [
                'body' => 'Customer changed number',
                'identity' => 'identity-1',
                'new_wa_id' => '26751111111',
                'type' => 'user_changed_number',
            ],
        ],
        [],
    );

    expect($message->location()?->name())->toBe('Store')
        ->and($message->location()?->longitude())->toBe(27.5)
        ->and($message->order()?->catalogId())->toBe('catalog-1')
        ->and($message->order()?->items()[0]->productRetailerId())->toBe('sku-123')
        ->and($message->order()?->items()[0]->quantity())->toBe(2)
        ->and($message->system()?->type())->toBe('user_changed_number')
        ->and($message->system()?->newWaId())->toBe('26751111111');
});
