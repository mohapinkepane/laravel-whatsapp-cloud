<?php

declare(strict_types=1);

use Mohapinkepane\WhatsAppCloud\Config\WhatsAppConfig;
use Mohapinkepane\WhatsAppCloud\Webhooks\WebhookRequestParser;

it('parses inbound messages and statuses with bsuid preference', function (): void {
    $payload = [
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
                        'user_id' => 'bsuid-001',
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
                        'status' => 'delivered',
                        'recipient_id' => '26750000000',
                        'conversation' => [
                            'id' => 'conversation-1',
                            'origin' => ['type' => 'user_initiated'],
                        ],
                        'pricing' => [
                            'billable' => true,
                            'category' => 'service',
                        ],
                        'errors' => [[
                            'code' => 131000,
                            'title' => 'Sample error',
                            'message' => 'Something happened',
                        ]],
                    ]],
                ],
            ]],
        ]],
    ];

    $parsed = (new WebhookRequestParser(WhatsAppConfig::fromArray([
        'phone_number_id' => '123456789',
        'webhook' => ['restrict_inbound_messages_to_phone_number_id' => true],
    ])))->parse($payload);

    expect($parsed->messages())->toHaveCount(2)
        ->and($parsed->statuses())->toHaveCount(1)
        ->and($parsed->messages()[0]->sender())->toBe('bsuid-001')
        ->and($parsed->messages()[0]->user()->phoneNumber())->toBe('26750000000')
        ->and($parsed->messages()[0]->text())->toBe('hello')
        ->and($parsed->messages()[1]->interactiveType())->toBe('button_reply')
        ->and($parsed->messages()[1]->buttonReplyId())->toBe('btn-1')
        ->and($parsed->statuses()[0]->status())->toBe('delivered');

    expect($parsed->statuses()[0]->conversationId())->toBe('conversation-1')
        ->and($parsed->statuses()[0]->conversationOriginType())->toBe('user_initiated')
        ->and($parsed->statuses()[0]->pricingCategory())->toBe('service')
        ->and($parsed->statuses()[0]->pricingBillable())->toBeTrue()
        ->and($parsed->statuses()[0]->errors())->toHaveCount(1)
        ->and($parsed->statuses()[0]->errors()[0]->code())->toBe(131000);
});

it('extracts list and flow reply helpers from incoming messages', function (): void {
    $payload = [
        'entry' => [[
            'changes' => [[
                'value' => [
                    'metadata' => [
                        'phone_number_id' => '123456789',
                    ],
                    'contacts' => [[
                        'wa_id' => '26750000000',
                    ]],
                    'messages' => [[
                        'id' => 'wamid.list',
                        'from' => '26750000000',
                        'type' => 'interactive',
                        'interactive' => [
                            'type' => 'list_reply',
                            'list_reply' => [
                                'id' => 'row-1',
                                'title' => 'Flights',
                            ],
                            'nfm_reply' => [
                                'response_json' => [
                                    'flow_token' => 'flow-123',
                                    'step' => 'complete',
                                ],
                            ],
                        ],
                    ]],
                ],
            ]],
        ]],
    ];

    $message = (new WebhookRequestParser(WhatsAppConfig::fromArray([
        'phone_number_id' => '123456789',
        'webhook' => ['restrict_inbound_messages_to_phone_number_id' => true],
    ])))->parse($payload)->messages()[0];

    expect($message->listReplyId())->toBe('row-1')
        ->and($message->listReplyTitle())->toBe('Flights')
        ->and($message->flowToken())->toBe('flow-123')
        ->and($message->flowResponse())->toBe([
            'flow_token' => 'flow-123',
            'step' => 'complete',
        ]);
});

it('ignores webhook entries from other phone number ids when restriction is enabled', function (): void {
    $payload = [
        'entry' => [[
            'changes' => [[
                'value' => [
                    'metadata' => [
                        'phone_number_id' => 'other-phone-number-id',
                    ],
                    'messages' => [[
                        'id' => 'wamid.filtered',
                        'from' => '26750000000',
                        'type' => 'text',
                        'text' => ['body' => 'hello'],
                    ]],
                    'statuses' => [[
                        'id' => 'wamid.status.filtered',
                        'status' => 'sent',
                    ]],
                ],
            ]],
        ]],
    ];

    $parsed = (new WebhookRequestParser(WhatsAppConfig::fromArray([
        'phone_number_id' => '123456789',
        'webhook' => ['restrict_inbound_messages_to_phone_number_id' => true],
    ])))->parse($payload);

    expect($parsed->messages())->toBe([])
        ->and($parsed->statuses())->toBe([]);
});

it('can process webhook entries from other phone number ids when restriction is disabled', function (): void {
    $payload = [
        'entry' => [[
            'changes' => [[
                'value' => [
                    'metadata' => [
                        'phone_number_id' => 'other-phone-number-id',
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

    $parsed = (new WebhookRequestParser(WhatsAppConfig::fromArray([
        'phone_number_id' => '123456789',
        'webhook' => ['restrict_inbound_messages_to_phone_number_id' => false],
    ])))->parse($payload);

    expect($parsed->messages())->toHaveCount(1)
        ->and($parsed->messages()[0]->id())->toBe('wamid.allowed');
});
