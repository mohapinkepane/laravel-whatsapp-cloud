<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Inbound;

final readonly class IncomingMessage
{
    /**
     * @param  array<string, mixed>  $payload
     * @param  array<string, mixed>  $metadata
     */
    public function __construct(
        private string $id,
        private string $type,
        private IncomingUser $user,
        private array $payload,
        private array $metadata,
    ) {}

    public function id(): string
    {
        return $this->id;
    }

    public function type(): string
    {
        return $this->type;
    }

    public function sender(): string
    {
        return $this->user->id();
    }

    public function user(): IncomingUser
    {
        return $this->user;
    }

    public function text(): ?string
    {
        $text = $this->payload['text']['body'] ?? null;

        return is_string($text) ? $text : null;
    }

    public function reaction(): ?IncomingReaction
    {
        return IncomingReaction::fromArray($this->payload);
    }

    public function media(): ?IncomingMedia
    {
        return IncomingMedia::fromArray($this->payload);
    }

    public function location(): ?IncomingLocation
    {
        return IncomingLocation::fromArray($this->payload);
    }

    public function order(): ?IncomingOrder
    {
        return IncomingOrder::fromArray($this->payload);
    }

    public function system(): ?IncomingSystemMessage
    {
        return IncomingSystemMessage::fromArray($this->payload);
    }

    /**
     * @return array<int, IncomingContactCard>
     */
    public function contacts(): array
    {
        $contacts = $this->payload['contacts'] ?? null;

        if (! is_array($contacts)) {
            return [];
        }

        return array_values(array_filter(array_map(
            IncomingContactCard::fromValue(...),
            $contacts,
        )));
    }

    public function interactiveReply(): ?IncomingInteractiveReply
    {
        return IncomingInteractiveReply::fromArray($this->payload);
    }

    public function interactiveType(): ?string
    {
        $type = $this->payload['interactive']['type'] ?? null;

        return is_string($type) ? $type : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function interactive(): ?array
    {
        $interactive = $this->payload['interactive'] ?? null;

        return is_array($interactive) ? $interactive : null;
    }

    public function buttonReplyId(): ?string
    {
        $id = $this->payload['interactive']['button_reply']['id'] ?? null;

        return is_string($id) ? $id : null;
    }

    public function buttonReplyTitle(): ?string
    {
        $title = $this->payload['interactive']['button_reply']['title'] ?? null;

        return is_string($title) ? $title : null;
    }

    public function listReplyId(): ?string
    {
        $id = $this->payload['interactive']['list_reply']['id'] ?? null;

        return is_string($id) ? $id : null;
    }

    public function listReplyTitle(): ?string
    {
        $title = $this->payload['interactive']['list_reply']['title'] ?? null;

        return is_string($title) ? $title : null;
    }

    public function flowToken(): ?string
    {
        $token = $this->payload['flow_token'] ?? $this->payload['interactive']['nfm_reply']['response_json']['flow_token'] ?? null;

        return is_string($token) ? $token : null;
    }

    /**
     * @return array<string, mixed>|null
     */
    public function flowResponse(): ?array
    {
        $response = $this->payload['interactive']['nfm_reply']['response_json'] ?? null;

        return is_array($response) ? $response : null;
    }

    /**
     * @return array<string, mixed>
     */
    public function payload(): array
    {
        return $this->payload;
    }

    /**
     * @return array<string, mixed>
     */
    public function metadata(): array
    {
        return $this->metadata;
    }

    public function extra(string $key, mixed $default = null): mixed
    {
        return $this->payload[$key] ?? $default;
    }
}
