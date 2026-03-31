<?php

declare(strict_types=1);

use Mohapinkepane\WhatsAppCloud\Components\TemplateComponent;
use Mohapinkepane\WhatsAppCloud\Messages\TemplateMessage;

it('serializes a template message with body components', function (): void {
    $payload = TemplateMessage::create('purchase_receipt', 'en_US')
        ->addComponent(TemplateComponent::textBody('John Doe', '$100.00'))
        ->toArray();

    expect($payload)->toBe([
        'messaging_product' => 'whatsapp',
        'type' => 'template',
        'template' => [
            'name' => 'purchase_receipt',
            'language' => ['code' => 'en_US'],
            'components' => [
                [
                    'type' => 'body',
                    'parameters' => [
                        ['type' => 'text', 'text' => 'John Doe'],
                        ['type' => 'text', 'text' => '$100.00'],
                    ],
                ],
            ],
        ],
    ]);
});

it('serializes richer template component types', function (): void {
    $payload = TemplateMessage::create('promo_template', 'en_US')
        ->addComponents([
            TemplateComponent::headerImage('https://example.com/banner.jpg'),
            TemplateComponent::create('body', [
                TemplateComponent::currencyParameter('$10.00', 'USD', 10000),
                TemplateComponent::dateTimeParameter('Tomorrow'),
            ]),
            TemplateComponent::quickReplyButton(0, 'confirm-order'),
            TemplateComponent::urlButton(1, 'order-123'),
            TemplateComponent::copyCodeButton(2, 'PROMO2026'),
        ])
        ->toArray();

    expect($payload['template']['components'])->toHaveCount(5)
        ->and($payload['template']['components'][0]['parameters'][0]['type'])->toBe('image')
        ->and($payload['template']['components'][1]['parameters'][0]['type'])->toBe('currency')
        ->and($payload['template']['components'][2]['sub_type'])->toBe('quick_reply')
        ->and($payload['template']['components'][4]['parameters'][0]['coupon_code'])->toBe('PROMO2026');
});
