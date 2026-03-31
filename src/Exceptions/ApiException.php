<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Exceptions;

use Illuminate\Http\Client\Response;

class ApiException extends WhatsAppException
{
    /**
     * @param  array<string, mixed>  $context
     */
    public function __construct(string $message, protected readonly int $statusCode, protected readonly array $context = [])
    {
        parent::__construct($message, $statusCode);
    }

    public static function fromResponse(Response $response): self
    {
        /** @var array<string, mixed> $decoded */
        $decoded = $response->json() ?? [];
        $error = $decoded['error'] ?? [];

        $message = is_array($error) && isset($error['message']) && is_string($error['message'])
            ? $error['message']
            : 'The WhatsApp Cloud API request failed.';

        return new self($message, $response->status(), $decoded);
    }

    public function statusCode(): int
    {
        return $this->statusCode;
    }

    /**
     * @return array<string, mixed>
     */
    public function context(): array
    {
        return $this->context;
    }
}
