<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Inbound;

use LogicException;
use Mohapinkepane\WhatsAppCloud\Support\Recipient;

final readonly class IncomingUser
{
    public function __construct(
        private string $id,
        private ?string $userId,
        private ?string $parentUserId,
        private ?string $phoneNumber,
        private ?string $waId,
        private ?string $name,
    ) {}

    /**
     * @param  array<string, mixed>  $message
     * @param  array<string, mixed>|null  $contact
     */
    public static function fromWebhook(array $message, ?array $contact = null): self
    {
        $userId = self::stringOrNull($message['user_id'] ?? $message['from_user_id'] ?? null);
        $parentUserId = self::stringOrNull($message['parent_user_id'] ?? $message['from_parent_user_id'] ?? null);
        $waId = self::stringOrNull($contact['wa_id'] ?? $message['wa_id'] ?? null);
        $phoneNumber = self::stringOrNull($waId ?? $message['from'] ?? null);
        $name = self::stringOrNull($contact['profile']['name'] ?? null);
        $id = $userId ?? $parentUserId ?? $phoneNumber ?? 'unknown';

        return new self($id, $userId, $parentUserId, $phoneNumber, $waId, $name);
    }

    public function id(): string
    {
        return $this->id;
    }

    public function identifier(): string
    {
        return $this->id;
    }

    public function businessScopedId(): ?string
    {
        return $this->userId ?? $this->parentUserId;
    }

    public function businessScopedUserId(): ?string
    {
        return $this->businessScopedId();
    }

    public function recipient(): Recipient
    {
        $businessScopedId = $this->businessScopedId();

        if ($businessScopedId !== null) {
            return Recipient::businessScopedUser($businessScopedId);
        }

        if ($this->phoneNumber !== null) {
            return Recipient::phoneNumber($this->phoneNumber);
        }

        if ($this->waId !== null) {
            return Recipient::phoneNumber($this->waId);
        }

        throw new LogicException('Incoming user does not contain a sendable recipient identifier.');
    }

    public function recipientIdentifier(): string
    {
        return $this->recipient()->value();
    }

    public function recipientField(): string
    {
        return $this->recipient()->requestField();
    }

    public function userId(): ?string
    {
        return $this->userId;
    }

    public function parentUserId(): ?string
    {
        return $this->parentUserId;
    }

    public function phoneNumber(): ?string
    {
        return $this->phoneNumber;
    }

    public function waId(): ?string
    {
        return $this->waId;
    }

    public function name(): ?string
    {
        return $this->name;
    }

    private static function stringOrNull(mixed $value): ?string
    {
        return is_string($value) && $value !== '' ? $value : null;
    }
}
