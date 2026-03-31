<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Components;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class ListSection implements ProvidesWhatsAppPayload
{
    /**
     * @param  array<int, ListRow>  $rows
     */
    private function __construct(
        private string $title,
        private array $rows,
    ) {}

    /**
     * @param  array<int, ListRow>  $rows
     */
    public static function create(string $title, array $rows): self
    {
        return new self($title, $rows);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return [
            'title' => $this->title,
            'rows' => array_map(
                static fn (ListRow $row): array => $row->toArray(),
                $this->rows,
            ),
        ];
    }
}
