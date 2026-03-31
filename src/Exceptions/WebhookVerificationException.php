<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Exceptions;

final class WebhookVerificationException extends WhatsAppException
{
    public static function invalidVerifyToken(): self
    {
        return new self('The WhatsApp webhook verify token is invalid.');
    }

    public static function invalidSignature(): self
    {
        return new self('The WhatsApp webhook signature is invalid.');
    }
}
