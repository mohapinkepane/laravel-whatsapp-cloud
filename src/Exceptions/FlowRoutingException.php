<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Exceptions;

final class FlowRoutingException extends WhatsAppException
{
    public static function routeNotFound(?string $action, ?string $screen): self
    {
        return new self(sprintf(
            'No flow route matched action [%s] and screen [%s].',
            $action ?? 'null',
            $screen ?? 'null',
        ));
    }
}
