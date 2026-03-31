<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Support;

abstract class Recipient
{
    public function __construct(protected readonly string $value) {}

    public static function phoneNumber(string $phoneNumber): PhoneNumberRecipient
    {
        return new PhoneNumberRecipient($phoneNumber);
    }

    public static function businessScopedUser(string $businessScopedUserId): BusinessScopedUserRecipient
    {
        return new BusinessScopedUserRecipient($businessScopedUserId);
    }

    public function value(): string
    {
        return $this->value;
    }

    public function field(): string
    {
        return $this->requestField();
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return [$this->requestField() => $this->value()];
    }

    abstract public function requestField(): string;
}
