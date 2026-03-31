<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Components;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class FlowActionPayload implements ProvidesWhatsAppPayload
{
    /**
     * @param  array<string, mixed>  $data
     */
    private function __construct(
        private string $screen,
        private array $data,
    ) {}

    /**
     * @param  array<string, mixed>  $data
     */
    public static function create(string $screen, array $data = []): self
    {
        return new self($screen, $data);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'screen' => $this->screen,
            'data' => $this->data,
        ];
    }
}
