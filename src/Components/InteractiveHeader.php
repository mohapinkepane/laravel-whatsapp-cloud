<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Components;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class InteractiveHeader implements ProvidesWhatsAppPayload
{
    /**
     * @param  array<string, mixed>  $content
     */
    private function __construct(
        private string $type,
        private array $content,
    ) {}

    public static function text(string $text): self
    {
        return new self('text', ['text' => $text]);
    }

    public static function imageLink(string $url): self
    {
        return new self('image', ['image' => ['link' => $url]]);
    }

    public static function videoLink(string $url): self
    {
        return new self('video', ['video' => ['link' => $url]]);
    }

    public static function documentLink(string $url, ?string $filename = null): self
    {
        $payload = ['document' => ['link' => $url]];

        if ($filename !== null) {
            $payload['document']['filename'] = $filename;
        }

        return new self('document', $payload);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_merge(['type' => $this->type], $this->content);
    }
}
