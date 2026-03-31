<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Events;

use Mohapinkepane\WhatsAppCloud\Inbound\IncomingMessage;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingOrder;

final readonly class OrderReceived
{
    public function __construct(
        public IncomingMessage $message,
        public IncomingOrder $order,
    ) {}
}
