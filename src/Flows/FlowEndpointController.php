<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Flows;

use Illuminate\Http\Request;
use Mohapinkepane\WhatsAppCloud\Flows\Contracts\ValidatesFlowToken;
use Symfony\Component\HttpFoundation\Response;

abstract class FlowEndpointController
{
    public function __construct(
        private readonly FlowCrypto $crypto,
        private readonly ?ValidatesFlowToken $tokenValidator = null,
    ) {}

    public function handleFlow(Request $request): Response
    {
        $flowRequest = $this->crypto->decryptRequest($request);
        $payloadBody = $flowRequest->body();

        $this->validateFlowToken($payloadBody);

        $payload = $this->resolveFlowResponse($payloadBody);
        $encrypted = $this->crypto->encryptResponse($payload, $flowRequest->aesKey(), $flowRequest->initialVector());

        return new Response($encrypted, 200, ['Content-Type' => 'text/plain']);
    }

    /**
     * @param  array<string, mixed>  $decryptedBody
     * @return array<string, mixed>
     */
    protected function resolveFlowResponse(array $decryptedBody): array
    {
        $router = $this->flowRouter();

        if ($router instanceof FlowRouter) {
            return $router->dispatch($decryptedBody);
        }

        return $this->getNextScreen($decryptedBody);
    }

    protected function expectedFlowToken(): ?string
    {
        return null;
    }

    protected function flowRouter(): ?FlowRouter
    {
        return null;
    }

    /**
     * @param  array<string, mixed>  $decryptedBody
     */
    protected function validateFlowToken(array $decryptedBody): void
    {
        $this->tokenValidator?->validate($decryptedBody, $this->expectedFlowToken());
    }

    /**
     * @param  array<string, mixed>  $decryptedBody
     * @return array<string, mixed>
     */
    abstract protected function getNextScreen(array $decryptedBody): array;
}
