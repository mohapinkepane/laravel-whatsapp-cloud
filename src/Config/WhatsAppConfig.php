<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Config;

final readonly class WhatsAppConfig
{
    /**
     * @param  array<string, mixed>  $items
     */
    public function __construct(private array $items) {}

    /**
     * @param  array<string, mixed>  $items
     */
    public static function fromArray(array $items): self
    {
        return new self($items);
    }

    public function graphBaseUrl(): string
    {
        return rtrim((string) ($this->items['graph_base_url'] ?? 'https://graph.facebook.com'), '/');
    }

    public function graphApiVersion(): string
    {
        return trim((string) ($this->items['graph_api_version'] ?? 'v23.0'), '/');
    }

    public function accessToken(): ?string
    {
        $value = $this->items['access_token'] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function phoneNumberId(): ?string
    {
        $value = $this->items['phone_number_id'] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function webhookVerifyToken(): ?string
    {
        $value = $this->items['webhook']['verify_token'] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function webhookAppSecret(): ?string
    {
        $value = $this->items['webhook']['app_secret'] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function restrictInboundMessagesToPhoneNumberId(): bool
    {
        return (bool) ($this->items['webhook']['restrict_inbound_messages_to_phone_number_id'] ?? true);
    }

    public function flowPassphrase(): ?string
    {
        $value = $this->items['flow']['passphrase'] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function flowPublicKey(): ?string
    {
        $value = $this->items['flow']['public_key'] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function flowPrivateKey(): ?string
    {
        $value = $this->items['flow']['private_key'] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function flowMessageVersion(): string
    {
        return (string) ($this->items['flow']['message_version'] ?? '3');
    }

    public function notificationPhoneNumberId(): ?string
    {
        $value = $this->items['notifications']['default_phone_number_id'] ?? null;

        return is_string($value) && $value !== '' ? $value : null;
    }

    public function defaultPhoneNumberId(): ?string
    {
        return $this->notificationPhoneNumberId();
    }

    public function timeout(): int
    {
        return (int) ($this->items['http']['timeout'] ?? 30);
    }

    public function retryTimes(): int
    {
        return (int) ($this->items['http']['retry_times'] ?? 2);
    }

    public function retrySleepMilliseconds(): int
    {
        return (int) ($this->items['http']['retry_sleep_milliseconds'] ?? 200);
    }

    public function strictMode(): bool
    {
        return (bool) ($this->items['strict_mode'] ?? true);
    }

    /**
     * @return array<string, mixed>
     */
    public function conversationalComponents(): array
    {
        $value = $this->items['conversational_components'] ?? [];

        return is_array($value) ? $value : [];
    }

    public function endpointUrl(string $path = ''): string
    {
        $path = ltrim($path, '/');

        return $path === ''
            ? sprintf('%s/%s', $this->graphBaseUrl(), $this->graphApiVersion())
            : sprintf('%s/%s/%s', $this->graphBaseUrl(), $this->graphApiVersion(), $path);
    }

    /**
     * @return array<string, mixed>
     */
    public function all(): array
    {
        return $this->items;
    }
}
