<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Events;

use Mohapinkepane\WhatsAppCloud\Inbound\IncomingMessage;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingReaction;

final readonly class ReactionReceived
{
    public function __construct(
        public IncomingMessage $message,
        public IncomingReaction $reaction,
    ) {}
}
