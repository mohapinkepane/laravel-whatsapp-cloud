<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Exceptions;

class ValidationException extends WhatsAppException
{
    public static function missingConfiguration(string $key): self
    {
        return new self(sprintf('Missing WhatsApp configuration value [%s].', $key));
    }

    public static function invalidRecipient(): self
    {
        return new self('A WhatsApp recipient is required before sending a message.');
    }

    public static function invalidMessage(string $message): self
    {
        return new self($message);
    }
}
