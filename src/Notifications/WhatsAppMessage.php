<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Notifications;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Messages\RawPayloadMessage;
use Mohapinkepane\WhatsAppCloud\Messages\TextMessage;
use Mohapinkepane\WhatsAppCloud\Support\Recipient;

final class WhatsAppMessage
{
    private Recipient|string|null $recipient = null;

    private ?string $phoneNumberId = null;

    private function __construct(private readonly ProvidesWhatsAppPayload $payload) {}

    public static function text(string $text): self
    {
        return new self(TextMessage::create($text));
    }

    public static function using(ProvidesWhatsAppPayload $payload): self
    {
        return new self($payload);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function raw(array $payload): self
    {
        return new self(new RawPayloadMessage($payload));
    }

    public function to(Recipient|string $recipient): self
    {
        $clone = clone $this;
        $clone->recipient = $recipient;

        return $clone;
    }

    public function toRecipient(Recipient|string $recipient): self
    {
        return $this->to($recipient);
    }

    public function toPhoneNumber(string $phoneNumber): self
    {
        return $this->to(Recipient::phoneNumber($phoneNumber));
    }

    public function toBusinessScopedUser(string $businessScopedUserId): self
    {
        return $this->to(Recipient::businessScopedUser($businessScopedUserId));
    }

    public function phoneNumberId(string $phoneNumberId): self
    {
        $clone = clone $this;
        $clone->phoneNumberId = $phoneNumberId;

        return $clone;
    }

    public function payload(): ProvidesWhatsAppPayload
    {
        return $this->payload;
    }

    public function recipient(): Recipient|string|null
    {
        return $this->recipient;
    }

    public function resolvedPhoneNumberId(): ?string
    {
        return $this->phoneNumberId;
    }

    public function phoneNumberIdValue(): ?string
    {
        return $this->resolvedPhoneNumberId();
    }
}
