<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Inbound;

final readonly class IncomingMedia
{
    public function __construct(
        private string $type,
        private string $id,
        private ?string $mimeType,
        private ?string $sha256,
        private ?string $caption,
        private ?string $filename,
    ) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public static function fromArray(array $payload): ?self
    {
        $type = $payload['type'] ?? null;

        if (! is_string($type) || ! in_array($type, ['audio', 'document', 'image', 'sticker', 'video'], true)) {
            $type = null;

            foreach (['audio', 'document', 'image', 'sticker', 'video'] as $candidate) {
                if (is_array($payload[$candidate] ?? null) && is_string($payload[$candidate]['id'] ?? null)) {
                    $type = $candidate;

                    break;
                }
            }
        }

        if ($type === null) {
            return null;
        }

        $media = $payload[$type] ?? null;

        if (! is_array($media) || ! is_string($media['id'] ?? null)) {
            return null;
        }

        return new self(
            $type,
            $media['id'],
            is_string($media['mime_type'] ?? null) ? $media['mime_type'] : null,
            is_string($media['sha256'] ?? null) ? $media['sha256'] : null,
            is_string($media['caption'] ?? null) ? $media['caption'] : null,
            is_string($media['filename'] ?? null) ? $media['filename'] : null,
        );
    }

    public function type(): string
    {
        return $this->type;
    }

    public function id(): string
    {
        return $this->id;
    }

    public function mimeType(): ?string
    {
        return $this->mimeType;
    }

    public function sha256(): ?string
    {
        return $this->sha256;
    }

    public function caption(): ?string
    {
        return $this->caption;
    }

    public function filename(): ?string
    {
        return $this->filename;
    }
}
