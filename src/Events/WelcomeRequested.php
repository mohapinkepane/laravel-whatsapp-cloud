<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Events;

use Mohapinkepane\WhatsAppCloud\Inbound\IncomingMessage;

final readonly class WelcomeRequested
{
    public function __construct(public IncomingMessage $message) {}
}
