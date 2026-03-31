<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Messages;

use Mohapinkepane\WhatsAppCloud\Components\TemplateComponent;
use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Messages\Concerns\HasContext;

final class TemplateMessage implements ProvidesWhatsAppPayload
{
    use HasContext;

    /**
     * @var array<int, TemplateComponent>
     */
    private array $components = [];

    private function __construct(
        private readonly string $name,
        private readonly string $languageCode,
    ) {}

    public static function create(string $name, string $languageCode): self
    {
        return new self($name, $languageCode);
    }

    /**
     * @param  array<int, TemplateComponent>  $components
     */
    public function addComponents(array $components): self
    {
        $clone = clone $this;
        $clone->components = [...$clone->components, ...$components];

        return $clone;
    }

    public function addComponent(TemplateComponent $component): self
    {
        return $this->addComponents([$component]);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge($this->buildContextPayload(), [
            'messaging_product' => 'whatsapp',
            'type' => 'template',
            'template' => array_filter([
                'name' => $this->name,
                'language' => ['code' => $this->languageCode],
                'components' => $this->components === [] ? null : array_map(
                    static fn (TemplateComponent $component): array => $component->toArray(),
                    $this->components,
                ),
            ], static fn (mixed $value): bool => $value !== null),
        ]);
    }
}
