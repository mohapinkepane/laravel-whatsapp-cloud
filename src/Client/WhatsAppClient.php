<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Client;

use Illuminate\Http\Client\Factory;
use Illuminate\Http\Client\PendingRequest;
use Illuminate\Http\Client\Response;
use Mohapinkepane\WhatsAppCloud\Config\WhatsAppConfig;
use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Contracts\SendsWhatsAppMessages;
use Mohapinkepane\WhatsAppCloud\Exceptions\ApiException;
use Mohapinkepane\WhatsAppCloud\Exceptions\ValidationException;
use Mohapinkepane\WhatsAppCloud\Support\Recipient;

final readonly class WhatsAppClient implements SendsWhatsAppMessages
{
    public function __construct(
        private Factory $http,
        private WhatsAppConfig $config,
    ) {}

    /**
     * @param  array<string, mixed>|ProvidesWhatsAppPayload  $message
     */
    public function sendMessage(Recipient|string $recipient, array|ProvidesWhatsAppPayload $message, ?string $phoneNumberId = null): PendingSendResponse
    {
        return new PendingSendResponse(
            fn (array|ProvidesWhatsAppPayload $pendingMessage): Response => $this->dispatchSendMessage($recipient, $pendingMessage, $phoneNumberId),
            $message,
        );
    }

    /**
     * @param  array<string, mixed>|ProvidesWhatsAppPayload  $message
     */
    private function dispatchSendMessage(Recipient|string $recipient, array|ProvidesWhatsAppPayload $message, ?string $phoneNumberId = null): Response
    {
        $payload = $message instanceof ProvidesWhatsAppPayload ? $message->toArray() : $message;

        if (! isset($payload['type']) && $this->config->strictMode()) {
            throw ValidationException::invalidMessage('Outgoing WhatsApp payloads must declare a [type].');
        }

        $response = $this->request()->post(
            sprintf('%s/messages', $this->resolvePhoneNumberId($phoneNumberId)),
            array_merge($payload, $this->normalizeRecipient($recipient)->toArray(), [
                'messaging_product' => $payload['messaging_product'] ?? 'whatsapp',
            ]),
        );

        if ($response->failed()) {
            throw ApiException::fromResponse($response);
        }

        return $response;
    }

    public function markMessageAsRead(string $messageId, ?string $phoneNumberId = null): Response
    {
        return $this->dispatchMessageStatusUpdate($messageId, $phoneNumberId);
    }

    public function showTypingIndicator(string $messageId, ?string $phoneNumberId = null): Response
    {
        return $this->dispatchMessageStatusUpdate($messageId, $phoneNumberId, [
            'typing_indicator' => [
                'type' => 'text',
            ],
        ]);
    }

    public function typingIndicator(string $messageId, ?string $phoneNumberId = null): Response
    {
        return $this->showTypingIndicator($messageId, $phoneNumberId);
    }

    /**
     * @param  array<string, mixed>  $extraPayload
     */
    private function dispatchMessageStatusUpdate(string $messageId, ?string $phoneNumberId = null, array $extraPayload = []): Response
    {
        $response = $this->request()->post(
            sprintf('%s/messages', $this->resolvePhoneNumberId($phoneNumberId)),
            array_merge([
                'messaging_product' => 'whatsapp',
                'status' => 'read',
                'message_id' => $messageId,
            ], $extraPayload),
        );

        if ($response->failed()) {
            throw ApiException::fromResponse($response);
        }

        return $response;
    }

    public function media(string $mediaId): Response
    {
        $response = $this->request()->get(ltrim($mediaId, '/'));

        if ($response->failed()) {
            throw ApiException::fromResponse($response);
        }

        return $response;
    }

    public function mediaUrl(string $mediaId): ?string
    {
        $url = $this->media($mediaId)->json('url');

        return is_string($url) && $url !== '' ? $url : null;
    }

    public function uploadMedia(string $filePath, string $mimeType, ?string $filename = null, ?string $phoneNumberId = null): Response
    {
        if (! is_file($filePath)) {
            throw ValidationException::invalidMessage(sprintf('Media file [%s] does not exist.', $filePath));
        }

        $response = $this->multipartRequest()->attach(
            'file',
            file_get_contents($filePath),
            $filename ?? basename($filePath),
            ['Content-Type' => $mimeType],
        )->post(sprintf('%s/media', $this->resolvePhoneNumberId($phoneNumberId)), [
            'messaging_product' => 'whatsapp',
            'type' => $mimeType,
        ]);

        if ($response->failed()) {
            throw ApiException::fromResponse($response);
        }

        return $response;
    }

    public function deleteMedia(string $mediaId): Response
    {
        $response = $this->request()->delete(ltrim($mediaId, '/'));

        if ($response->failed()) {
            throw ApiException::fromResponse($response);
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function post(string $path, array $payload = []): Response
    {
        $response = $this->request()->post(ltrim($path, '/'), $payload);

        if ($response->failed()) {
            throw ApiException::fromResponse($response);
        }

        return $response;
    }

    public function get(string $path): Response
    {
        $response = $this->request()->get(ltrim($path, '/'));

        if ($response->failed()) {
            throw ApiException::fromResponse($response);
        }

        return $response;
    }

    public function delete(string $path): Response
    {
        $response = $this->request()->delete(ltrim($path, '/'));

        if ($response->failed()) {
            throw ApiException::fromResponse($response);
        }

        return $response;
    }

    /**
     * @param  array<string, mixed>|null  $payload
     */
    public function syncConversationalComponents(?string $phoneNumberId = null, ?array $payload = null): Response
    {
        return $this->post(
            sprintf('%s/conversational_automation', $this->resolvePhoneNumberId($phoneNumberId)),
            $payload ?? $this->config->conversationalComponents(),
        );
    }

    public function conversationalComponents(?string $phoneNumberId = null): Response
    {
        return $this->get(sprintf('%s/conversational_automation', $this->resolvePhoneNumberId($phoneNumberId)));
    }

    private function request(): PendingRequest
    {
        $token = $this->config->accessToken();

        if ($token === null) {
            throw ValidationException::missingConfiguration('whatsapp-cloud.access_token');
        }

        return $this->http
            ->baseUrl($this->config->endpointUrl())
            ->acceptJson()
            ->asJson()
            ->timeout($this->config->timeout())
            ->retry($this->config->retryTimes(), $this->config->retrySleepMilliseconds())
            ->withToken($token);
    }

    private function multipartRequest(): PendingRequest
    {
        $token = $this->config->accessToken();

        if ($token === null) {
            throw ValidationException::missingConfiguration('whatsapp-cloud.access_token');
        }

        return $this->http
            ->baseUrl($this->config->endpointUrl())
            ->acceptJson()
            ->timeout($this->config->timeout())
            ->retry($this->config->retryTimes(), $this->config->retrySleepMilliseconds())
            ->withToken($token);
    }

    private function resolvePhoneNumberId(?string $phoneNumberId = null): string
    {
        $resolved = $phoneNumberId ?? $this->config->notificationPhoneNumberId() ?? $this->config->phoneNumberId();

        if ($resolved === null || $resolved === '') {
            throw ValidationException::missingConfiguration('whatsapp-cloud.phone_number_id');
        }

        return $resolved;
    }

    private function normalizeRecipient(Recipient|string $recipient): Recipient
    {
        if ($recipient instanceof Recipient) {
            return $recipient;
        }

        if ($recipient === '') {
            throw ValidationException::invalidRecipient();
        }

        return Recipient::phoneNumber($recipient);
    }
}
