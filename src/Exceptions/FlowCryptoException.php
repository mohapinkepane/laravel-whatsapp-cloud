<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Exceptions;

final class FlowCryptoException extends WhatsAppException
{
    public static function missingKey(string $key): self
    {
        return new self(sprintf('Missing WhatsApp flow configuration value [%s].', $key));
    }

    public static function decryptionFailed(string $message): self
    {
        return new self($message);
    }

    public static function encryptionFailed(string $message): self
    {
        return new self($message);
    }
}
