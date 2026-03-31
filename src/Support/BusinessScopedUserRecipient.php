<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Support;

final class BusinessScopedUserRecipient extends Recipient
{
    public function requestField(): string
    {
        return 'recipient';
    }
}
