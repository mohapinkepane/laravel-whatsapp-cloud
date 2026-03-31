<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Event;
use Mohapinkepane\WhatsAppCloud\Config\WhatsAppConfig;
use Mohapinkepane\WhatsAppCloud\Events\InteractiveReplyReceived;
use Mohapinkepane\WhatsAppCloud\Events\MediaReceived;
use Mohapinkepane\WhatsAppCloud\Events\MessageReceived;
use Mohapinkepane\WhatsAppCloud\Events\OrderReceived;
use Mohapinkepane\WhatsAppCloud\Events\ReactionReceived;
use Mohapinkepane\WhatsAppCloud\Events\StatusUpdated;
use Mohapinkepane\WhatsAppCloud\Events\SystemMessageReceived;
use Mohapinkepane\WhatsAppCloud\Http\Controllers\BaseWhatsAppWebhookController;
use Mohapinkepane\WhatsAppCloud\Http\Controllers\WhatsAppWebhookController;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingMessage;
use Mohapinkepane\WhatsAppCloud\Webhooks\WebhookPayload;
use Mohapinkepane\WhatsAppCloud\Webhooks\WebhookRequestParser;
use Mohapinkepane\WhatsAppCloud\Webhooks\WebhookSignatureValidator;
use Mohapinkepane\WhatsAppCloud\Webhooks\WebhookStatus;

it('verifies webhook challenges', function (): void {
    $controller = resolve(WhatsAppWebhookController::class);
    $request = Request::create('/webhook', 'GET', [
        'hub_verify_token' => 'verify-me',
        'hub_challenge' => 'challenge-123',
    ]);

    $response = $controller->verify($request);

    expect($response->getContent())->toBe('challenge-123');
});

it('parses and dispatches inbound webhook events', function (): void {
    Event::fake([
        MessageReceived::class,
        ReactionReceived::class,
        MediaReceived::class,
        OrderReceived::class,
        SystemMessageReceived::class,
        InteractiveReplyReceived::class,
        StatusUpdated::class,
    ]);

    $body = [
        'entry' => [[
            'changes' => [[
                'value' => [
                    'metadata' => [
                        'phone_number_id' => '123456789',
                    ],
                    'contacts' => [[
                        'wa_id' => '26750000000',
                        'profile' => ['name' => 'Neo'],
                    ]],
                    'messages' => [[
                        'id' => 'wamid.inbound',
                        'from' => '26750000000',
                        'type' => 'text',
                        'text' => ['body' => 'hello'],
                    ], [
                        'id' => 'wamid.reaction',
                        'from' => '26750000000',
                        'type' => 'reaction',
                        'reaction' => [
                            'message_id' => 'wamid.origin',
                            'emoji' => '👍',
                        ],
                    ], [
                        'id' => 'wamid.media',
                        'from' => '26750000000',
                        'type' => 'image',
                        'image' => [
                            'id' => 'media-1',
                            'caption' => 'caption',
                        ],
                    ], [
                        'id' => 'wamid.order',
                        'from' => '26750000000',
                        'type' => 'order',
                        'order' => [
                            'catalog_id' => 'catalog-1',
                            'product_items' => [[
                                'product_retailer_id' => 'sku-123',
                            ]],
                        ],
                    ], [
                        'id' => 'wamid.system',
                        'from' => '26750000000',
                        'type' => 'system',
                        'system' => [
                            'body' => 'changed number',
                            'type' => 'user_changed_number',
                        ],
                    ], [
                        'id' => 'wamid.interactive',
                        'from' => '26750000000',
                        'type' => 'interactive',
                        'interactive' => [
                            'type' => 'button_reply',
                            'button_reply' => [
                                'id' => 'btn-1',
                                'title' => 'Continue',
                            ],
                        ],
                    ]],
                    'statuses' => [[
                        'id' => 'wamid.sent',
                        'status' => 'sent',
                        'recipient_id' => '26750000000',
                    ]],
                ],
            ]],
        ]],
    ];

    $content = json_encode($body, JSON_THROW_ON_ERROR);
    $signature = 'sha256='.hash_hmac('sha256', $content, 'app-secret');

    $request = Request::create('/webhook', 'POST', server: [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_HUB_SIGNATURE_256' => $signature,
    ], content: $content);

    $response = resolve(WhatsAppWebhookController::class)->handle($request);
    $decoded = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($decoded)->toBe([
        'received' => true,
        'messages' => 6,
        'statuses' => 1,
    ]);

    Event::assertDispatched(MessageReceived::class);
    Event::assertDispatched(ReactionReceived::class);
    Event::assertDispatched(MediaReceived::class);
    Event::assertDispatched(OrderReceived::class);
    Event::assertDispatched(SystemMessageReceived::class);
    Event::assertDispatched(InteractiveReplyReceived::class);
    Event::assertDispatched(StatusUpdated::class);
});

it('ignores inbound webhook payloads from other phone number ids when restriction is enabled', function (): void {
    Event::fake([
        MessageReceived::class,
        StatusUpdated::class,
    ]);

    config()->set('whatsapp-cloud.webhook.restrict_inbound_messages_to_phone_number_id', true);
    config()->set('whatsapp-cloud.phone_number_id', '123456789');

    $body = [
        'entry' => [[
            'changes' => [[
                'value' => [
                    'metadata' => [
                        'phone_number_id' => '999999999',
                    ],
                    'messages' => [[
                        'id' => 'wamid.filtered',
                        'from' => '26750000000',
                        'type' => 'text',
                        'text' => ['body' => 'hello'],
                    ]],
                    'statuses' => [[
                        'id' => 'wamid.filtered.status',
                        'status' => 'sent',
                        'recipient_id' => '26750000000',
                    ]],
                ],
            ]],
        ]],
    ];

    $content = json_encode($body, JSON_THROW_ON_ERROR);
    $signature = 'sha256='.hash_hmac('sha256', $content, 'app-secret');

    $request = Request::create('/webhook', 'POST', server: [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_HUB_SIGNATURE_256' => $signature,
    ], content: $content);

    $response = resolve(WhatsAppWebhookController::class)->handle($request);
    $decoded = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($decoded)->toBe([
        'received' => true,
        'messages' => 0,
        'statuses' => 0,
    ]);

    Event::assertNotDispatched(MessageReceived::class);
    Event::assertNotDispatched(StatusUpdated::class);
});

it('can process inbound webhook payloads from other phone number ids when restriction is disabled', function (): void {
    Event::fake([
        MessageReceived::class,
    ]);

    config()->set('whatsapp-cloud.webhook.restrict_inbound_messages_to_phone_number_id', false);
    config()->set('whatsapp-cloud.phone_number_id', '123456789');

    $body = [
        'entry' => [[
            'changes' => [[
                'value' => [
                    'metadata' => [
                        'phone_number_id' => '999999999',
                    ],
                    'messages' => [[
                        'id' => 'wamid.allowed',
                        'from' => '26750000000',
                        'type' => 'text',
                        'text' => ['body' => 'hello'],
                    ]],
                ],
            ]],
        ]],
    ];

    $content = json_encode($body, JSON_THROW_ON_ERROR);
    $signature = 'sha256='.hash_hmac('sha256', $content, 'app-secret');

    $request = Request::create('/webhook', 'POST', server: [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_HUB_SIGNATURE_256' => $signature,
    ], content: $content);

    $response = resolve(WhatsAppWebhookController::class)->handle($request);
    $decoded = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($decoded)->toBe([
        'received' => true,
        'messages' => 1,
        'statuses' => 0,
    ]);

    Event::assertDispatched(MessageReceived::class);
});

it('can be extended so applications handle inbound messages directly in a custom controller', function (): void {
    Event::fake([
        MessageReceived::class,
        StatusUpdated::class,
    ]);

    $controller = new class(resolve(WhatsAppConfig::class), resolve(WebhookSignatureValidator::class), resolve(WebhookRequestParser::class), resolve('events')) extends BaseWhatsAppWebhookController
    {
        /**
         * @var array<int, string>
         */
        public array $handledMessages = [];

        /**
         * @var array<int, string>
         */
        public array $handledStatuses = [];

        protected function handleIncomingMessage(Request $request, IncomingMessage $message, WebhookPayload $payload): void
        {
            $this->handledMessages[] = $message->id();
        }

        protected function handleIncomingStatus(Request $request, WebhookStatus $status, WebhookPayload $payload): void
        {
            $this->handledStatuses[] = $status->id();
        }
    };

    $body = [
        'entry' => [[
            'changes' => [[
                'value' => [
                    'metadata' => [
                        'phone_number_id' => '123456789',
                    ],
                    'messages' => [[
                        'id' => 'wamid.custom',
                        'from' => '26750000000',
                        'type' => 'text',
                        'text' => ['body' => 'hello'],
                    ]],
                    'statuses' => [[
                        'id' => 'wamid.custom.status',
                        'status' => 'sent',
                        'recipient_id' => '26750000000',
                    ]],
                ],
            ]],
        ]],
    ];

    $content = json_encode($body, JSON_THROW_ON_ERROR);
    $signature = 'sha256='.hash_hmac('sha256', $content, 'app-secret');

    $request = Request::create('/webhook', 'POST', server: [
        'CONTENT_TYPE' => 'application/json',
        'HTTP_X_HUB_SIGNATURE_256' => $signature,
    ], content: $content);

    $response = $controller->handle($request);
    $decoded = json_decode($response->getContent(), true, flags: JSON_THROW_ON_ERROR);

    expect($decoded)->toBe([
        'received' => true,
        'messages' => 1,
        'statuses' => 1,
    ])
        ->and($controller->handledMessages)->toBe(['wamid.custom'])
        ->and($controller->handledStatuses)->toBe(['wamid.custom.status']);

    Event::assertNotDispatched(MessageReceived::class);
    Event::assertNotDispatched(StatusUpdated::class);
});
