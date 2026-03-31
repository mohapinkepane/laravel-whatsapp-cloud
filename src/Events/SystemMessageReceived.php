<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Events;

use Mohapinkepane\WhatsAppCloud\Inbound\IncomingMessage;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingSystemMessage;

final readonly class SystemMessageReceived
{
    public function __construct(
        public IncomingMessage $message,
        public IncomingSystemMessage $system,
    ) {}
}
