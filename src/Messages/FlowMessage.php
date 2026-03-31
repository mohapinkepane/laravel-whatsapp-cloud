<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Messages;

use Mohapinkepane\WhatsAppCloud\Components\FlowActionPayload;
use Mohapinkepane\WhatsAppCloud\Components\InteractiveHeader;
use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Messages\Concerns\HasContext;

final class FlowMessage implements ProvidesWhatsAppPayload
{
    use HasContext;

    private ?InteractiveHeader $header = null;

    private ?string $footer = null;

    private ?FlowActionPayload $actionPayload = null;

    private function __construct(
        private readonly string $flowId,
        private readonly string $flowToken,
        private readonly string $callToAction,
        private readonly string $body,
        private readonly string $mode,
        private readonly string $action,
        private readonly string $version,
    ) {}

    public static function create(
        string $flowId,
        string $flowToken,
        string $callToAction,
        string $body,
        string $mode = 'published',
        string $action = 'navigate',
        string $version = '3',
    ): self {
        return new self($flowId, $flowToken, $callToAction, $body, $mode, $action, $version);
    }

    public function addHeader(InteractiveHeader $header): self
    {
        $clone = clone $this;
        $clone->header = $header;

        return $clone;
    }

    public function addFooter(string $footer): self
    {
        $clone = clone $this;
        $clone->footer = $footer;

        return $clone;
    }

    public function addActionPayload(FlowActionPayload $payload): self
    {
        $clone = clone $this;
        $clone->actionPayload = $payload;

        return $clone;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $parameters = [
            'flow_message_version' => $this->version,
            'flow_id' => $this->flowId,
            'flow_cta' => $this->callToAction,
            'flow_token' => $this->flowToken,
            'mode' => $this->mode,
            'flow_action' => $this->action,
        ];

        if ($this->actionPayload instanceof FlowActionPayload) {
            $parameters['flow_action_payload'] = $this->actionPayload->toArray();
        }

        return array_merge($this->buildContextPayload(), [
            'messaging_product' => 'whatsapp',
            'type' => 'interactive',
            'interactive' => array_filter([
                'type' => 'flow',
                'header' => $this->header?->toArray(),
                'body' => ['text' => $this->body],
                'footer' => $this->footer !== null ? ['text' => $this->footer] : null,
                'action' => [
                    'name' => 'flow',
                    'parameters' => $parameters,
                ],
            ], static fn (mixed $value): bool => $value !== null),
        ]);
    }
}
