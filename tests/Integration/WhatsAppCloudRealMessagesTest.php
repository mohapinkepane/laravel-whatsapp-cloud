<?php

declare(strict_types=1);

use Illuminate\Http\Client\Response;
use Mohapinkepane\WhatsAppCloud\Client\WhatsAppClient;
use Mohapinkepane\WhatsAppCloud\Components\FlowActionPayload;
use Mohapinkepane\WhatsAppCloud\Components\InteractiveHeader;
use Mohapinkepane\WhatsAppCloud\Components\ListRow;
use Mohapinkepane\WhatsAppCloud\Components\ListSection;
use Mohapinkepane\WhatsAppCloud\Components\ProductItem;
use Mohapinkepane\WhatsAppCloud\Components\ProductSection;
use Mohapinkepane\WhatsAppCloud\Components\ReplyButton;
use Mohapinkepane\WhatsAppCloud\Components\TemplateComponent;
use Mohapinkepane\WhatsAppCloud\Contacts\Address;
use Mohapinkepane\WhatsAppCloud\Contacts\Contact;
use Mohapinkepane\WhatsAppCloud\Contacts\Email;
use Mohapinkepane\WhatsAppCloud\Contacts\Name;
use Mohapinkepane\WhatsAppCloud\Contacts\Organization;
use Mohapinkepane\WhatsAppCloud\Contacts\Phone;
use Mohapinkepane\WhatsAppCloud\Contacts\Url;
use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Messages\CallToActionUrlMessage;
use Mohapinkepane\WhatsAppCloud\Messages\ContactsMessage;
use Mohapinkepane\WhatsAppCloud\Messages\FlowMessage;
use Mohapinkepane\WhatsAppCloud\Messages\ListMessage;
use Mohapinkepane\WhatsAppCloud\Messages\LocationMessage;
use Mohapinkepane\WhatsAppCloud\Messages\LocationRequestMessage;
use Mohapinkepane\WhatsAppCloud\Messages\MediaMessage;
use Mohapinkepane\WhatsAppCloud\Messages\ProductListMessage;
use Mohapinkepane\WhatsAppCloud\Messages\ProductMessage;
use Mohapinkepane\WhatsAppCloud\Messages\ReactionMessage;
use Mohapinkepane\WhatsAppCloud\Messages\ReplyButtonsMessage;
use Mohapinkepane\WhatsAppCloud\Messages\TemplateMessage;
use Mohapinkepane\WhatsAppCloud\Messages\TextMessage;
use Mohapinkepane\WhatsAppCloud\Support\Recipient;
use Mohapinkepane\WhatsAppCloud\Tests\Integration\Support\RealWhatsAppTestConfig;
use PHPUnit\Framework\TestCase;

function realWhatsAppConfig(): RealWhatsAppTestConfig
{
    static $config;

    return $config ??= RealWhatsAppTestConfig::fromEnvironment();
}

function realWhatsAppClient(): WhatsAppClient
{
    return resolve(WhatsAppClient::class);
}

function ensureRealWhatsAppPreflight(TestCase $test): void
{
    static $result = null;

    if ($result === null) {
        $config = realWhatsAppConfig();
        $config->ensureCoreConfigured($test);

        try {
            realWhatsAppClient()->get($config->phoneNumberId());

            $result = [
                'ok' => true,
                'message' => null,
                'reported' => false,
            ];
        } catch (Throwable $throwable) {
            $result = [
                'ok' => false,
                'message' => $throwable->getMessage(),
                'reported' => false,
            ];
        }
    }

    if ($result['ok'] === true) {
        return;
    }

    if ($result['reported'] === false) {
        $result['reported'] = true;

        $test->fail('WhatsApp integration preflight failed: '.$result['message']);
    }

    $test->markTestSkipped('Skipping after WhatsApp integration preflight failure: '.$result['message']);
}

function sendRealWhatsAppMessage(string|Recipient $recipient, ProvidesWhatsAppPayload $message): string
{
    return acceptedWhatsAppMessageId(realWhatsAppClient()->sendMessage($recipient, $message));
}

function acceptedWhatsAppMessageId(Response $response): string
{
    expect($response->successful())->toBeTrue();

    $messageId = $response->json('messages.0.id');

    expect($messageId)->toBeString()->toMatch('/.+/');

    $status = $response->json('messages.0.message_status');

    if (is_string($status)) {
        expect($status === 'failed')->toBeFalse();
    }

    return $messageId;
}

function integrationContact(): Contact
{
    return Contact::create(
        [Address::create('Menlo Park', 'United States')],
        '2012-08-18',
        [
            Email::create('integration@example.com'),
            Email::create('support@example.com', 'WORK'),
        ],
        Name::create('Integration', 'Integration Test Contact', 'Contact'),
        Organization::create('WhatsApp Cloud API', 'QA'),
        [
            Phone::create('+1 (940) 555-1234', 'WORK', '19405551234'),
        ],
        [
            Url::create('https://developers.facebook.com/docs/whatsapp/cloud-api'),
        ],
    );
}

dataset('real outbound message builders', [
    'plain text' => [
        static fn (RealWhatsAppTestConfig $config): ProvidesWhatsAppPayload => TextMessage::create(
            'Integration test plain text sent at '.gmdate(DATE_ATOM),
        ),
    ],
    'url preview text' => [
        static fn (RealWhatsAppTestConfig $config): ProvidesWhatsAppPayload => TextMessage::create(
            'Integration test preview https://developers.facebook.com/docs/whatsapp/cloud-api/guides/send-messages',
        )->previewUrl(),
    ],
    'image media' => [
        static fn (RealWhatsAppTestConfig $config): ProvidesWhatsAppPayload => MediaMessage::create('image')
            ->url($config->mediaUrl('image'))
            ->caption('Integration test image'),
    ],
    'audio media' => [
        static fn (RealWhatsAppTestConfig $config): ProvidesWhatsAppPayload => MediaMessage::create('audio')
            ->url($config->mediaUrl('audio')),
    ],
    'document media' => [
        static fn (RealWhatsAppTestConfig $config): ProvidesWhatsAppPayload => MediaMessage::create('document')
            ->url($config->mediaUrl('document'))
            ->caption('Integration test document')
            ->filename('integration-test.pdf'),
    ],
    'sticker media' => [
        static fn (RealWhatsAppTestConfig $config): ProvidesWhatsAppPayload => MediaMessage::create('sticker')
            ->url($config->mediaUrl('sticker')),
    ],
    'video media' => [
        static fn (RealWhatsAppTestConfig $config): ProvidesWhatsAppPayload => MediaMessage::create('video')
            ->url($config->mediaUrl('video'))
            ->caption('Integration test video'),
    ],
    'contacts' => [
        static fn (RealWhatsAppTestConfig $config): ProvidesWhatsAppPayload => ContactsMessage::create([integrationContact()]),
    ],
    'location' => [
        static fn (RealWhatsAppTestConfig $config): ProvidesWhatsAppPayload => LocationMessage::create(
            $config->locationLongitude(),
            $config->locationLatitude(),
            $config->locationName(),
            $config->locationAddress(),
        ),
    ],
    'location request' => [
        static fn (RealWhatsAppTestConfig $config): ProvidesWhatsAppPayload => LocationRequestMessage::create(
            'Integration test: please share your location when convenient.',
        ),
    ],
    'reply buttons' => [
        static fn (RealWhatsAppTestConfig $config): ProvidesWhatsAppPayload => ReplyButtonsMessage::create(
            'Integration test: which experience best describes this package?',
        )
            ->addHeader(InteractiveHeader::text('Real reply buttons'))
            ->addFooter('Laravel WhatsApp Cloud API')
            ->addButtons([
                ReplyButton::create('fast', 'Fast'),
                ReplyButton::create('clear', 'Clear'),
                ReplyButton::create('solid', 'Solid'),
            ]),
    ],
    'interactive list' => [
        static fn (RealWhatsAppTestConfig $config): ProvidesWhatsAppPayload => ListMessage::create(
            'Integration test: browse the live message cases exercised by this suite.',
            'Open list',
        )
            ->addHeader(InteractiveHeader::text('Real interactive list'))
            ->addFooter('Laravel WhatsApp Cloud API')
            ->addSection(ListSection::create('Core coverage', [
                ListRow::create('text', 'Text and context')->description('Plain text, previews, replies, and reactions'),
                ListRow::create('media', 'Media messages')->description('Image, audio, document, sticker, and video'),
            ]))
            ->addSection(ListSection::create('Extended coverage', [
                ListRow::create('contacts', 'Contacts and location')->description('Structured contact cards and map pins'),
                ListRow::create('interactive', 'Interactive messages')->description('Buttons, lists, flows, and commerce when configured'),
            ])),
    ],
    'call to action url' => [
        static fn (RealWhatsAppTestConfig $config): ProvidesWhatsAppPayload => CallToActionUrlMessage::create(
            'Integration test: open the package repository for docs and source.',
            'View package',
            'https://github.com/mohapinkepane/laravel-whatsapp-cloud',
        )
            ->addHeader(InteractiveHeader::text('Real call to action'))
            ->addFooter('Laravel WhatsApp Cloud API'),
    ],
]);

it('sends real outbound WhatsApp messages across the supported core builders', function (callable $messageBuilder): void {
    ensureRealWhatsAppPreflight($this);

    $config = realWhatsAppConfig();

    $messageId = sendRealWhatsAppMessage($config->recipient(), $messageBuilder($config));

    expect($messageId)->toBeString()->toMatch('/.+/');
})->with('real outbound message builders');

it('sends contextual follow-up messages and reactions against a live thread', function (): void {
    ensureRealWhatsAppPreflight($this);

    $config = realWhatsAppConfig();

    $anchorMessageId = sendRealWhatsAppMessage(
        $config->recipient(),
        TextMessage::create('Integration test anchor message for context and reaction at '.gmdate(DATE_ATOM)),
    );

    $replyMessageId = sendRealWhatsAppMessage(
        $config->recipient(),
        TextMessage::create('Integration test contextual reply')->contextMessageId($anchorMessageId),
    );

    $reactionMessageId = sendRealWhatsAppMessage(
        $config->recipient(),
        ReactionMessage::create($anchorMessageId, '😀'),
    );

    expect($replyMessageId)->toBeString()->toMatch('/.+/')
        ->and($reactionMessageId)->toBeString()->toMatch('/.+/');
});

it('sends template messages when template coverage is configured', function (): void {
    ensureRealWhatsAppPreflight($this);

    $config = realWhatsAppConfig();
    $config->ensureTemplateConfigured($this);

    $message = TemplateMessage::create($config->templateName(), $config->templateLanguage());

    $bodyValues = $config->templateBodyValues();

    if ($bodyValues !== []) {
        $message = $message->addComponent(TemplateComponent::textBody(...$bodyValues));
    }

    $messageId = sendRealWhatsAppMessage($config->recipient(), $message);

    expect($messageId)->toBeString()->toMatch('/.+/');
});

it('sends flow messages when flow coverage is configured', function (): void {
    ensureRealWhatsAppPreflight($this);

    $config = realWhatsAppConfig();
    $config->ensureFlowConfigured($this);

    $message = FlowMessage::create(
        $config->flowId(),
        $config->flowToken(),
        $config->flowCallToAction(),
        $config->flowBody(),
        $config->flowMode(),
        $config->flowAction(),
    )->addFooter('Laravel WhatsApp Cloud API');

    if ($config->flowScreen() !== null) {
        $message = $message->addActionPayload(
            FlowActionPayload::create($config->flowScreen(), $config->flowData()),
        );
    }

    $messageId = sendRealWhatsAppMessage($config->recipient(), $message);

    expect($messageId)->toBeString()->toMatch('/.+/');
});

it('sends commerce messages when catalog coverage is configured', function (): void {
    ensureRealWhatsAppPreflight($this);

    $config = realWhatsAppConfig();
    $config->ensureCommerceConfigured($this);

    $retailerIds = $config->productRetailerIds();

    $singleProductMessageId = sendRealWhatsAppMessage(
        $config->recipient(),
        ProductMessage::create(
            'Integration test featured product',
            $config->catalogId(),
            $retailerIds[0],
        )
            ->addHeader(InteractiveHeader::text('Featured product'))
            ->addFooter('Laravel WhatsApp Cloud API'),
    );

    $productList = ProductListMessage::create(
        'Integration test product list',
        $config->catalogId(),
    )->addFooter('Laravel WhatsApp Cloud API');

    $sections = array_chunk($retailerIds, 2);

    foreach ($sections as $index => $sectionRetailerIds) {
        $productList = $productList->addSection(ProductSection::create(
            'Section '.($index + 1),
            array_map(
                ProductItem::create(...),
                $sectionRetailerIds,
            ),
        ));
    }

    $productListMessageId = sendRealWhatsAppMessage($config->recipient(), $productList);

    expect($singleProductMessageId)->toBeString()->toMatch('/.+/')
        ->and($productListMessageId)->toBeString()->toMatch('/.+/');
});

it('sends to a business-scoped recipient when one is configured', function (): void {
    ensureRealWhatsAppPreflight($this);

    $config = realWhatsAppConfig();
    $config->ensureBusinessScopedRecipientConfigured($this);

    $messageId = sendRealWhatsAppMessage(
        Recipient::businessScopedUser($config->businessScopedRecipient()),
        TextMessage::create('Integration test message sent via business-scoped recipient at '.gmdate(DATE_ATOM)),
    );

    expect($messageId)->toBeString()->toMatch('/.+/');
});
