<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Messages;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Exceptions\ValidationException;
use Mohapinkepane\WhatsAppCloud\Messages\Concerns\HasContext;

final class MediaMessage implements ProvidesWhatsAppPayload
{
    use HasContext;

    private ?string $id = null;

    private ?string $link = null;

    private ?string $caption = null;

    private ?string $filename = null;

    private function __construct(private readonly string $type)
    {
        if (! in_array($type, ['audio', 'document', 'image', 'sticker', 'video'], true)) {
            throw ValidationException::invalidMessage(sprintf('Unsupported media type [%s].', $type));
        }
    }

    public static function create(string $type): self
    {
        return new self($type);
    }

    public function id(string $id): self
    {
        $clone = clone $this;
        $clone->id = $id;
        $clone->link = null;

        return $clone;
    }

    public function url(string $url): self
    {
        $clone = clone $this;
        $clone->link = $url;
        $clone->id = null;

        return $clone;
    }

    public function caption(string $caption): self
    {
        $clone = clone $this;
        $clone->caption = $caption;

        return $clone;
    }

    public function filename(string $filename): self
    {
        $clone = clone $this;
        $clone->filename = $filename;

        return $clone;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        if ($this->id === null && $this->link === null) {
            throw ValidationException::invalidMessage('Media messages require either a media id or a URL.');
        }

        $media = array_filter([
            'id' => $this->id,
            'link' => $this->link,
            'caption' => in_array($this->type, ['document', 'image', 'video'], true) ? $this->caption : null,
            'filename' => $this->type === 'document' ? $this->filename : null,
        ], static fn (mixed $value): bool => $value !== null);

        return array_merge($this->buildContextPayload(), [
            'messaging_product' => 'whatsapp',
            'type' => $this->type,
            $this->type => $media,
        ]);
    }
}
