<?php

declare(strict_types=1);

use Mohapinkepane\WhatsAppCloud\Components\ListRow;
use Mohapinkepane\WhatsAppCloud\Components\ListSection;
use Mohapinkepane\WhatsAppCloud\Components\ProductItem;
use Mohapinkepane\WhatsAppCloud\Components\ProductSection;
use Mohapinkepane\WhatsAppCloud\Components\ReplyButton;
use Mohapinkepane\WhatsAppCloud\Config\WhatsAppConfig;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingUser;
use Mohapinkepane\WhatsAppCloud\Messages\ListMessage;
use Mohapinkepane\WhatsAppCloud\Messages\ProductListMessage;
use Mohapinkepane\WhatsAppCloud\Messages\ReplyButtonsMessage;
use Mohapinkepane\WhatsAppCloud\Notifications\WhatsAppMessage;
use Mohapinkepane\WhatsAppCloud\Support\Recipient;

it('provides explicit aliases for recipient routing helpers', function (): void {
    $user = IncomingUser::fromWebhook([
        'user_id' => 'bsuid-123',
        'from' => '26750000000',
    ]);

    $recipient = Recipient::businessScopedUser('bsuid-123');

    expect($user->businessScopedUserId())->toBe($user->businessScopedId())
        ->and($recipient->field())->toBe($recipient->requestField());
});

it('offers symmetric singular and plural builder methods', function (): void {
    $replyPayload = ReplyButtonsMessage::create('Choose one')
        ->addButton(ReplyButton::create('first', 'First'))
        ->addButtons([ReplyButton::create('second', 'Second')])
        ->toArray();

    $listPayload = ListMessage::create('Pick an option', 'View')
        ->addSections([
            ListSection::create('Main', [
                ListRow::create('1', 'Flights'),
            ]),
        ])
        ->addSection(ListSection::create('Extra', [
            ListRow::create('2', 'Hotels'),
        ]))
        ->toArray();

    $productListPayload = ProductListMessage::create('Browse products', 'CATALOG_ID')
        ->addSections([
            ProductSection::create('Popular', [
                ProductItem::create('SKU-123'),
            ]),
        ])
        ->addSection(ProductSection::create('New', [
            ProductItem::create('SKU-456'),
        ]))
        ->toArray();

    expect($replyPayload['interactive']['action']['buttons'])->toHaveCount(2)
        ->and($listPayload['interactive']['action']['sections'])->toHaveCount(2)
        ->and($productListPayload['interactive']['action']['sections'])->toHaveCount(2);
});

it('keeps explicit aliases for notification and config phone number ids', function (): void {
    $message = WhatsAppMessage::text('Hello')->phoneNumberId('987654321')->toRecipient('26750000000');
    $config = WhatsAppConfig::fromArray([
        'notifications' => ['default_phone_number_id' => '123456789'],
    ]);

    expect($message->phoneNumberIdValue())->toBe($message->resolvedPhoneNumberId())
        ->and($config->defaultPhoneNumberId())->toBe($config->notificationPhoneNumberId())
        ->and($message->recipient())->toBe('26750000000');
});
