<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Http\Controllers;

use Illuminate\Contracts\Events\Dispatcher;
use Illuminate\Http\Request;
use Mohapinkepane\WhatsAppCloud\Config\WhatsAppConfig;
use Mohapinkepane\WhatsAppCloud\Events\InteractiveReplyReceived;
use Mohapinkepane\WhatsAppCloud\Events\MediaReceived;
use Mohapinkepane\WhatsAppCloud\Events\MessageReceived;
use Mohapinkepane\WhatsAppCloud\Events\OrderReceived;
use Mohapinkepane\WhatsAppCloud\Events\ReactionReceived;
use Mohapinkepane\WhatsAppCloud\Events\StatusUpdated;
use Mohapinkepane\WhatsAppCloud\Events\SystemMessageReceived;
use Mohapinkepane\WhatsAppCloud\Events\WelcomeRequested;
use Mohapinkepane\WhatsAppCloud\Exceptions\WebhookVerificationException;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingInteractiveReply;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingMedia;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingMessage;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingOrder;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingReaction;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingSystemMessage;
use Mohapinkepane\WhatsAppCloud\Webhooks\WebhookPayload;
use Mohapinkepane\WhatsAppCloud\Webhooks\WebhookRequestParser;
use Mohapinkepane\WhatsAppCloud\Webhooks\WebhookSignatureValidator;
use Mohapinkepane\WhatsAppCloud\Webhooks\WebhookStatus;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;

abstract class BaseWhatsAppWebhookController
{
    public function __construct(
        protected readonly WhatsAppConfig $config,
        protected readonly WebhookSignatureValidator $signatureValidator,
        protected readonly WebhookRequestParser $parser,
        protected readonly Dispatcher $events,
    ) {}

    public function verify(Request $request): Response
    {
        if ($request->query('hub_verify_token') !== $this->config->webhookVerifyToken()) {
            throw WebhookVerificationException::invalidVerifyToken();
        }

        return new Response((string) $request->query('hub_challenge'));
    }

    public function handle(Request $request): JsonResponse
    {
        $this->ensureValidSignature($request);

        $webhookPayload = $this->parseWebhookPayload($request);

        return $this->handleWebhookPayload($request, $webhookPayload);
    }

    protected function ensureValidSignature(Request $request): void
    {
        if (! $this->signatureValidator->isValid($request)) {
            throw WebhookVerificationException::invalidSignature();
        }
    }

    protected function parseWebhookPayload(Request $request): WebhookPayload
    {
        /** @var array<string, mixed> $payload */
        $payload = $request->json()->all();

        return $this->parser->parse($payload);
    }

    protected function handleWebhookPayload(Request $request, WebhookPayload $payload): JsonResponse
    {
        foreach ($payload->messages() as $message) {
            $this->handleIncomingMessage($request, $message, $payload);
        }

        foreach ($payload->statuses() as $status) {
            $this->handleIncomingStatus($request, $status, $payload);
        }

        return $this->webhookResponse($request, $payload);
    }

    protected function handleIncomingMessage(Request $request, IncomingMessage $message, WebhookPayload $payload): void
    {
        if ($message->type() === 'request_welcome') {
            $this->dispatchWelcomeRequested($message);

            return;
        }

        $this->dispatchMessageReceived($message);
        $this->dispatchSpecializedMessageEvents($message);
    }

    protected function handleIncomingStatus(Request $request, WebhookStatus $status, WebhookPayload $payload): void
    {
        $this->dispatchStatusUpdated($status);
    }

    protected function webhookResponse(Request $request, WebhookPayload $payload): JsonResponse
    {
        return new JsonResponse([
            'received' => true,
            'messages' => count($payload->messages()),
            'statuses' => count($payload->statuses()),
        ]);
    }

    protected function dispatchWelcomeRequested(IncomingMessage $message): void
    {
        $this->events->dispatch(new WelcomeRequested($message));
    }

    protected function dispatchMessageReceived(IncomingMessage $message): void
    {
        $this->events->dispatch(new MessageReceived($message));
    }

    protected function dispatchStatusUpdated(WebhookStatus $status): void
    {
        $this->events->dispatch(new StatusUpdated($status));
    }

    protected function dispatchSpecializedMessageEvents(IncomingMessage $message): void
    {
        $reaction = $message->reaction();

        if ($reaction instanceof IncomingReaction) {
            $this->events->dispatch(new ReactionReceived($message, $reaction));
        }

        $media = $message->media();

        if ($media instanceof IncomingMedia) {
            $this->events->dispatch(new MediaReceived($message, $media));
        }

        $order = $message->order();

        if ($order instanceof IncomingOrder) {
            $this->events->dispatch(new OrderReceived($message, $order));
        }

        $system = $message->system();

        if ($system instanceof IncomingSystemMessage) {
            $this->events->dispatch(new SystemMessageReceived($message, $system));
        }

        $reply = $message->interactiveReply();

        if ($reply instanceof IncomingInteractiveReply) {
            $this->events->dispatch(new InteractiveReplyReceived($message, $reply));
        }
    }
}
