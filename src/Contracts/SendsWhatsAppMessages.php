<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Contracts;

use Illuminate\Http\Client\Response;
use Mohapinkepane\WhatsAppCloud\Support\Recipient;

interface SendsWhatsAppMessages
{
    /**
     * @param  array<string, mixed>|ProvidesWhatsAppPayload  $message
     */
    public function sendMessage(Recipient|string $recipient, array|ProvidesWhatsAppPayload $message, ?string $phoneNumberId = null): Response;
}
