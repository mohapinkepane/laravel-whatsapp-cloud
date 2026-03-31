<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Facades;

use Illuminate\Http\Client\Response;
use Illuminate\Support\Facades\Facade;
use Mohapinkepane\WhatsAppCloud\Client\PendingSendResponse;
use Mohapinkepane\WhatsAppCloud\Client\WhatsAppClient;
use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Support\Recipient;

/**
 * @method static PendingSendResponse sendMessage(Recipient|string $recipient, array<string, mixed>|ProvidesWhatsAppPayload $message, ?string $phoneNumberId = null)
 * @method static Response markMessageAsRead(string $messageId, ?string $phoneNumberId = null)
 * @method static Response showTypingIndicator(string $messageId, ?string $phoneNumberId = null)
 * @method static Response typingIndicator(string $messageId, ?string $phoneNumberId = null)
 * @method static Response uploadMedia(string $filePath, string $mimeType, ?string $filename = null, ?string $phoneNumberId = null)
 * @method static Response media(string $mediaId)
 * @method static string|null mediaUrl(string $mediaId)
 * @method static Response deleteMedia(string $mediaId)
 * @method static Response conversationalComponents(?string $phoneNumberId = null)
 * @method static Response syncConversationalComponents(?string $phoneNumberId = null, ?array<string, mixed> $payload = null)
 * @method static Response get(string $path)
 * @method static Response post(string $path, array<string, mixed> $payload = [])
 * @method static Response delete(string $path)
 */
final class WhatsAppCloud extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return WhatsAppClient::class;
    }
}
