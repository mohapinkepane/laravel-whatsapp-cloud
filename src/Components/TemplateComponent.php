<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Components;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class TemplateComponent implements ProvidesWhatsAppPayload
{
    /**
     * @param  array<int, array<string, mixed>>  $parameters
     */
    private function __construct(
        private string $type,
        private array $parameters,
        private ?string $subType = null,
        private ?int $index = null,
    ) {}

    /**
     * @param  array<int, array<string, mixed>>  $parameters
     */
    public static function create(string $type, array $parameters = [], ?string $subType = null, ?int $index = null): self
    {
        return new self($type, $parameters, $subType, $index);
    }

    public static function textBody(string ...$values): self
    {
        return new self(
            'body',
            array_map(
                static fn (string $value): array => ['type' => 'text', 'text' => $value],
                $values,
            ),
        );
    }

    public static function headerText(string $value): self
    {
        return new self('header', [self::textParameter($value)]);
    }

    public static function headerImage(string $url): self
    {
        return new self('header', [self::imageParameter($url)]);
    }

    public static function headerVideo(string $url): self
    {
        return new self('header', [self::videoParameter($url)]);
    }

    public static function headerDocument(string $url, ?string $filename = null): self
    {
        return new self('header', [self::documentParameter($url, $filename)]);
    }

    public static function currencyBody(string $fallbackValue, string $code, int $amount1000): self
    {
        return new self('body', [self::currencyParameter($fallbackValue, $code, $amount1000)]);
    }

    public static function dateTimeBody(string $fallbackValue): self
    {
        return new self('body', [self::dateTimeParameter($fallbackValue)]);
    }

    public static function quickReplyButton(int $index, string $payload): self
    {
        return new self('button', [self::payloadParameter($payload)], 'quick_reply', $index);
    }

    public static function urlButton(int $index, string $text): self
    {
        return new self('button', [self::textParameter($text)], 'url', $index);
    }

    public static function copyCodeButton(int $index, string $code): self
    {
        return new self('button', [self::couponCodeParameter($code)], 'copy_code', $index);
    }

    /**
     * @return array<string, mixed>
     */
    public static function textParameter(string $value): array
    {
        return ['type' => 'text', 'text' => $value];
    }

    /**
     * @return array<string, mixed>
     */
    public static function imageParameter(string $url): array
    {
        return ['type' => 'image', 'image' => ['link' => $url]];
    }

    /**
     * @return array<string, mixed>
     */
    public static function videoParameter(string $url): array
    {
        return ['type' => 'video', 'video' => ['link' => $url]];
    }

    /**
     * @return array<string, mixed>
     */
    public static function documentParameter(string $url, ?string $filename = null): array
    {
        $document = ['link' => $url];

        if ($filename !== null) {
            $document['filename'] = $filename;
        }

        return ['type' => 'document', 'document' => $document];
    }

    /**
     * @return array<string, mixed>
     */
    public static function currencyParameter(string $fallbackValue, string $code, int $amount1000): array
    {
        return [
            'type' => 'currency',
            'currency' => [
                'fallback_value' => $fallbackValue,
                'code' => $code,
                'amount_1000' => $amount1000,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function dateTimeParameter(string $fallbackValue): array
    {
        return [
            'type' => 'date_time',
            'date_time' => [
                'fallback_value' => $fallbackValue,
            ],
        ];
    }

    /**
     * @return array<string, mixed>
     */
    public static function payloadParameter(string $payload): array
    {
        return ['type' => 'payload', 'payload' => $payload];
    }

    /**
     * @return array<string, mixed>
     */
    public static function couponCodeParameter(string $code): array
    {
        return ['type' => 'coupon_code', 'coupon_code' => $code];
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        $payload = [
            'type' => $this->type,
            'parameters' => $this->parameters,
        ];

        if ($this->subType !== null) {
            $payload['sub_type'] = $this->subType;
        }

        if ($this->index !== null) {
            $payload['index'] = (string) $this->index;
        }

        return $payload;
    }
}
