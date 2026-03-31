<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Contracts;

interface ProvidesWhatsAppPayload
{
    /**
     * @return array<string, mixed>
     */
    public function toArray(): array;
}
