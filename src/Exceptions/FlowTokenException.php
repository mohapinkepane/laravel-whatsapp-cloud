<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Exceptions;

final class FlowTokenException extends WhatsAppException
{
    public static function missingToken(): self
    {
        return new self('The flow request does not contain a flow token.');
    }

    public static function invalidToken(): self
    {
        return new self('The flow token is invalid.');
    }
}
