<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Tests\Integration\Support;

use Illuminate\Support\Env;
use Illuminate\Support\Facades\Request;
use PHPUnit\Framework\TestCase;

final class RealWhatsAppTestConfig
{
    /**
     * @var array<string, string>|null
     */
    private static ?array $fileValues = null;

    private const DEFAULT_MEDIA_URLS = [
        'image' => 'https://images.pexels.com/photos/1266810/pexels-photo-1266810.jpeg',
        'audio' => 'https://samplelib.com/lib/preview/mp3/sample-15s.mp3',
        'document' => 'https://pdfobject.com/pdf/sample.pdf',
        'sticker' => 'https://stickermaker.s3.eu-west-1.amazonaws.com/storage/uploads/sticker-pack/meme-pack-3/sticker_18.webp',
        'video' => 'https://samplelib.com/lib/preview/mp4/sample-5s.mp4',
    ];

    private function __construct() {}

    public static function fromEnvironment(): self
    {
        return new self;
    }

    public function graphBaseUrl(): ?string
    {
        return $this->string('WHATSAPP_GRAPH_BASE_URL');
    }

    public function graphApiVersion(): ?string
    {
        return $this->string('WHATSAPP_GRAPH_API_VERSION');
    }

    public function accessToken(): ?string
    {
        return $this->string('WHATSAPP_ACCESS_TOKEN');
    }

    public function phoneNumberId(): ?string
    {
        return $this->string('WHATSAPP_PHONE_NUMBER_ID');
    }

    public function recipient(): ?string
    {
        return $this->string('WHATSAPP_TEST_RECIPIENT');
    }

    public function businessScopedRecipient(): ?string
    {
        return $this->string('WHATSAPP_TEST_BUSINESS_SCOPED_RECIPIENT');
    }

    public function httpTimeout(): ?int
    {
        return $this->integer('WHATSAPP_HTTP_TIMEOUT');
    }

    public function retryTimes(): ?int
    {
        return $this->integer('WHATSAPP_HTTP_RETRY_TIMES');
    }

    public function retrySleepMilliseconds(): ?int
    {
        return $this->integer('WHATSAPP_HTTP_RETRY_SLEEP_MS');
    }

    public function mediaUrl(string $type): string
    {
        $override = match ($type) {
            'image' => $this->string('WHATSAPP_TEST_MEDIA_IMAGE_URL'),
            'audio' => $this->string('WHATSAPP_TEST_MEDIA_AUDIO_URL'),
            'document' => $this->string('WHATSAPP_TEST_MEDIA_DOCUMENT_URL'),
            'sticker' => $this->string('WHATSAPP_TEST_MEDIA_STICKER_URL'),
            'video' => $this->string('WHATSAPP_TEST_MEDIA_VIDEO_URL'),
            default => null,
        };

        return $override ?? self::DEFAULT_MEDIA_URLS[$type];
    }

    public function locationLatitude(): float
    {
        return $this->float('WHATSAPP_TEST_LOCATION_LATITUDE') ?? 37.758056;
    }

    public function locationLongitude(): float
    {
        return $this->float('WHATSAPP_TEST_LOCATION_LONGITUDE') ?? -122.425332;
    }

    public function locationName(): string
    {
        return $this->string('WHATSAPP_TEST_LOCATION_NAME') ?? 'WhatsApp Cloud Integration Test';
    }

    public function locationAddress(): string
    {
        return $this->string('WHATSAPP_TEST_LOCATION_ADDRESS') ?? '1 Hacker Way, Menlo Park, CA 94025';
    }

    public function templateName(): string
    {
        return $this->string('WHATSAPP_TEST_TEMPLATE_NAME') ?? 'hello_world';
    }

    public function templateLanguage(): string
    {
        return $this->string('WHATSAPP_TEST_TEMPLATE_LANGUAGE') ?? 'en_US';
    }

    /**
     * @return array<int, string>
     */
    public function templateBodyValues(): array
    {
        return $this->list('WHATSAPP_TEST_TEMPLATE_BODY_VALUES', '|');
    }

    public function hasTemplate(): bool
    {
        return $this->boolean('WHATSAPP_TEST_TEMPLATE_ENABLED') || $this->string('WHATSAPP_TEST_TEMPLATE_NAME') !== null;
    }

    public function flowId(): ?string
    {
        return $this->string('WHATSAPP_TEST_FLOW_ID');
    }

    public function flowToken(): ?string
    {
        return $this->string('WHATSAPP_TEST_FLOW_TOKEN');
    }

    public function flowCallToAction(): string
    {
        return $this->string('WHATSAPP_TEST_FLOW_CTA') ?? 'Open flow';
    }

    public function flowBody(): string
    {
        return $this->string('WHATSAPP_TEST_FLOW_BODY') ?? 'Integration test flow message';
    }

    public function flowMode(): string
    {
        return $this->string('WHATSAPP_TEST_FLOW_MODE') ?? 'published';
    }

    public function flowAction(): string
    {
        return $this->string('WHATSAPP_TEST_FLOW_ACTION') ?? 'navigate';
    }

    public function flowScreen(): ?string
    {
        return $this->string('WHATSAPP_TEST_FLOW_SCREEN');
    }

    /**
     * @return array<string, mixed>
     */
    public function flowData(): array
    {
        $data = $this->string('WHATSAPP_TEST_FLOW_DATA_JSON');

        if ($data === null) {
            return [];
        }

        $decoded = json_decode($data, true);

        return is_array($decoded) ? $decoded : [];
    }

    public function hasFlow(): bool
    {
        return $this->flowId() !== null && $this->flowToken() !== null;
    }

    public function catalogId(): ?string
    {
        return $this->string('WHATSAPP_TEST_CATALOG_ID');
    }

    public function productRetailerId(): ?string
    {
        return $this->string('WHATSAPP_TEST_PRODUCT_RETAILER_ID');
    }

    /**
     * @return array<int, string>
     */
    public function productRetailerIds(): array
    {
        $ids = $this->list('WHATSAPP_TEST_PRODUCT_RETAILER_IDS');

        if ($ids !== []) {
            return $ids;
        }

        return array_filter([$this->productRetailerId()]);
    }

    public function hasCommerce(): bool
    {
        return $this->catalogId() !== null && $this->productRetailerIds() !== [];
    }

    public function ensureCoreConfigured(TestCase $test): void
    {
        $missing = array_filter([
            $this->accessToken() === null ? 'WHATSAPP_ACCESS_TOKEN' : null,
            $this->phoneNumberId() === null ? 'WHATSAPP_PHONE_NUMBER_ID' : null,
            $this->recipient() === null ? 'WHATSAPP_TEST_RECIPIENT' : null,
        ]);

        if ($missing !== []) {
            $test->markTestSkipped(sprintf(
                'Live WhatsApp integration tests require %s.',
                implode(', ', $missing),
            ));
        }
    }

    public function ensureTemplateConfigured(TestCase $test): void
    {
        if (! $this->hasTemplate()) {
            $test->markTestSkipped('Set WHATSAPP_TEST_TEMPLATE_ENABLED=true or WHATSAPP_TEST_TEMPLATE_NAME to run template integration coverage.');
        }
    }

    public function ensureFlowConfigured(TestCase $test): void
    {
        if (! $this->hasFlow()) {
            $test->markTestSkipped('Set WHATSAPP_TEST_FLOW_ID and WHATSAPP_TEST_FLOW_TOKEN to run flow integration coverage.');
        }
    }

    public function ensureCommerceConfigured(TestCase $test): void
    {
        if (! $this->hasCommerce()) {
            $test->markTestSkipped('Set WHATSAPP_TEST_CATALOG_ID and WHATSAPP_TEST_PRODUCT_RETAILER_ID or WHATSAPP_TEST_PRODUCT_RETAILER_IDS to run commerce integration coverage.');
        }
    }

    public function ensureBusinessScopedRecipientConfigured(TestCase $test): void
    {
        if ($this->businessScopedRecipient() === null) {
            $test->markTestSkipped('Set WHATSAPP_TEST_BUSINESS_SCOPED_RECIPIENT to run business-scoped recipient coverage.');
        }
    }

    private function string(string $key): ?string
    {
        $value = Env::get($key, Request::server($key) ?? getenv($key));

        if (! is_string($value)) {
            $value = $this->fileValues()[$key] ?? null;
        }

        if (! is_string($value)) {
            return null;
        }

        $value = trim($value);

        return $value === '' ? null : $value;
    }

    /**
     * @return array<string, string>
     */
    private function fileValues(): array
    {
        if (self::$fileValues !== null) {
            return self::$fileValues;
        }

        $path = dirname(__DIR__, 3).'/.env.integration';

        if (! is_file($path)) {
            return self::$fileValues = [];
        }

        $values = [];

        foreach (file($path, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES) ?: [] as $line) {
            $line = trim($line);

            if ($line === '' || str_starts_with($line, '#') || ! str_contains($line, '=')) {
                continue;
            }

            [$name, $value] = explode('=', $line, 2);

            $name = trim($name);
            $value = trim($value);

            if ($name === '') {
                continue;
            }

            if (
                (str_starts_with($value, '"') && str_ends_with($value, '"'))
                || (str_starts_with($value, "'") && str_ends_with($value, "'"))
            ) {
                $value = substr($value, 1, -1);
            }

            $values[$name] = $value;
        }

        return self::$fileValues = $values;
    }

    private function integer(string $key): ?int
    {
        $value = $this->string($key);

        return $value !== null && is_numeric($value) ? (int) $value : null;
    }

    private function float(string $key): ?float
    {
        $value = $this->string($key);

        return $value !== null && is_numeric($value) ? (float) $value : null;
    }

    private function boolean(string $key): bool
    {
        $value = $this->string($key);

        return in_array(strtolower((string) $value), ['1', 'true', 'yes', 'on'], true);
    }

    /**
     * @return array<int, string>
     */
    private function list(string $key, string $separator = ','): array
    {
        $value = $this->string($key);

        if ($value === null) {
            return [];
        }

        return array_values(array_filter(array_map(
            trim(...),
            explode($separator, $value),
        ), static fn (string $item): bool => $item !== ''));
    }
}
