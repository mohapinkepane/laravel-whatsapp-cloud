<?php

declare(strict_types=1);

use Mohapinkepane\WhatsAppCloud\Inbound\IncomingMessage;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingUser;

it('exposes typed inbound reaction, media, contacts, and interactive reply dto helpers', function (): void {
    $message = new IncomingMessage(
        'wamid.1',
        'interactive',
        new IncomingUser('user-1', null, null, '26750000000', '26750000000', 'Neo'),
        [
            'type' => 'interactive',
            'reaction' => [
                'message_id' => 'wamid.origin',
                'emoji' => '👍',
            ],
            'image' => [
                'id' => 'media-1',
                'mime_type' => 'image/jpeg',
                'sha256' => 'abc123',
                'caption' => 'Nice image',
            ],
            'contacts' => [[
                'name' => [
                    'formatted_name' => 'John Smith',
                ],
            ]],
            'interactive' => [
                'type' => 'list_reply',
                'list_reply' => [
                    'id' => 'row-1',
                    'title' => 'Flights',
                    'description' => 'Book flights',
                ],
                'nfm_reply' => [
                    'response_json' => [
                        'flow_token' => 'flow-123',
                    ],
                ],
            ],
        ],
        [],
    );

    expect($message->reaction()?->messageId())->toBe('wamid.origin')
        ->and($message->reaction()?->emoji())->toBe('👍')
        ->and($message->media()?->id())->toBe('media-1')
        ->and($message->media()?->caption())->toBe('Nice image')
        ->and($message->contacts()[0]->formattedName())->toBe('John Smith')
        ->and($message->interactiveReply()?->type())->toBe('list_reply')
        ->and($message->interactiveReply()?->id())->toBe('row-1')
        ->and($message->interactiveReply()?->description())->toBe('Book flights')
        ->and($message->interactiveReply()?->flowResponse())->toBe(['flow_token' => 'flow-123']);
});
