<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Contacts;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class Organization implements ProvidesWhatsAppPayload
{
    public function __construct(
        private string $company,
        private ?string $department = null,
        private ?string $title = null,
    ) {}

    public static function create(string $company, ?string $title = null, ?string $department = null): self
    {
        return new self($company, $department, $title);
    }

    /**
     * @return array<string, string>
     */
    public function toArray(): array
    {
        return array_filter([
            'company' => $this->company,
            'department' => $this->department,
            'title' => $this->title,
        ], static fn (mixed $value): bool => $value !== null);
    }
}
