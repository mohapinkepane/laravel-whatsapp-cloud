<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Support;

final class PhoneNumberRecipient extends Recipient
{
    public function requestField(): string
    {
        return 'to';
    }
}
