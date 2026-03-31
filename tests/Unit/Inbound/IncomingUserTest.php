<?php

declare(strict_types=1);

use Mohapinkepane\WhatsAppCloud\Inbound\IncomingUser;

it('returns a ready-to-send business scoped recipient when available', function (): void {
    $user = IncomingUser::fromWebhook([
        'from' => '26750000000',
        'user_id' => 'bsuid-123',
        'parent_user_id' => 'parent-456',
    ], [
        'wa_id' => '26751111111',
        'profile' => ['name' => 'Neo'],
    ]);

    expect($user->identifier())->toBe('bsuid-123')
        ->and($user->businessScopedId())->toBe('bsuid-123')
        ->and($user->userId())->toBe('bsuid-123')
        ->and($user->parentUserId())->toBe('parent-456')
        ->and($user->phoneNumber())->toBe('26751111111')
        ->and($user->waId())->toBe('26751111111')
        ->and($user->name())->toBe('Neo')
        ->and($user->recipientField())->toBe('recipient')
        ->and($user->recipientIdentifier())->toBe('bsuid-123')
        ->and($user->recipient()->requestField())->toBe('recipient')
        ->and($user->recipient()->value())->toBe('bsuid-123');
});

it('falls back to parent user id and then phone numbers for outbound routing', function (): void {
    $parentUser = IncomingUser::fromWebhook([
        'from_parent_user_id' => 'parent-456',
        'from' => '26750000000',
    ]);

    $phoneUser = IncomingUser::fromWebhook([
        'from' => '26750000000',
    ]);

    expect($parentUser->identifier())->toBe('parent-456')
        ->and($parentUser->businessScopedId())->toBe('parent-456')
        ->and($parentUser->recipientField())->toBe('recipient')
        ->and($parentUser->recipientIdentifier())->toBe('parent-456')
        ->and($phoneUser->identifier())->toBe('26750000000')
        ->and($phoneUser->businessScopedId())->toBeNull()
        ->and($phoneUser->recipientField())->toBe('to')
        ->and($phoneUser->recipientIdentifier())->toBe('26750000000');
});

it('throws a clear exception when no sendable identifier exists', function (): void {
    $user = new IncomingUser('unknown', null, null, null, null, null);

    expect(fn (): string => $user->recipientIdentifier())
        ->toThrow(LogicException::class, 'Incoming user does not contain a sendable recipient identifier.');
});
