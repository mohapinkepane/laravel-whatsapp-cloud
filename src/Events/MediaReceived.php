<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Events;

use Mohapinkepane\WhatsAppCloud\Inbound\IncomingMedia;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingMessage;

final readonly class MediaReceived
{
    public function __construct(
        public IncomingMessage $message,
        public IncomingMedia $media,
    ) {}
}
