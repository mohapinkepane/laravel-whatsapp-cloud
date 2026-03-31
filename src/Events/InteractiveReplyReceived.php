<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Events;

use Mohapinkepane\WhatsAppCloud\Inbound\IncomingInteractiveReply;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingMessage;

final readonly class InteractiveReplyReceived
{
    public function __construct(
        public IncomingMessage $message,
        public IncomingInteractiveReply $reply,
    ) {}
}
