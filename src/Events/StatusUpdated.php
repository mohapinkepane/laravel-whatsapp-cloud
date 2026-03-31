<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Events;

use Mohapinkepane\WhatsAppCloud\Webhooks\WebhookStatus;

final readonly class StatusUpdated
{
    public function __construct(public WebhookStatus $status) {}
}
