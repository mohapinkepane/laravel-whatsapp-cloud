<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Webhooks;

final readonly class WebhookStatus
{
    /**
     * @param  array<string, mixed>  $payload
     */
    public function __construct(
        private string $id,
        private string $status,
        private ?string $recipientId,
        private ?string $conversationId,
        private ?string $conversationOriginType,
        private ?string $pricingCategory,
        private ?bool $pricingBillable,
        /**
         * @var array<int, WebhookError>
         */
        private array $errors,
        private array $payload,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function status(): string
    {
        return $this->status;
    }

    public function recipientId(): ?string
    {
        return $this->recipientId;
    }

    public function conversationId(): ?string
    {
        return $this->conversationId;
    }

    public function conversationOriginType(): ?string
    {
        return $this->conversationOriginType;
    }

    public function pricingCategory(): ?string
    {
        return $this->pricingCategory;
    }

    public function pricingBillable(): ?bool
    {
        return $this->pricingBillable;
    }

    /**
     * @return array<int, WebhookError>
     */
    public function errors(): array
    {
        return $this->errors;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }
}
