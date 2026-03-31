<?php

declare(strict_types=1);

use Mohapinkepane\WhatsAppCloud\Exceptions\FlowTokenException;
use Mohapinkepane\WhatsAppCloud\Flows\FlowCrypto;
use Mohapinkepane\WhatsAppCloud\Flows\FlowEndpointController;
use Mohapinkepane\WhatsAppCloud\Flows\FlowRouter;
use Mohapinkepane\WhatsAppCloud\Flows\FlowTokenValidator;

it('routes flow payloads through the flow router', function (): void {
    $controller = new class(resolve(FlowCrypto::class), resolve(FlowTokenValidator::class)) extends FlowEndpointController
    {
        protected function expectedFlowToken(): string
        {
            return 'token-123';
        }

        protected function flowRouter(): FlowRouter
        {
            return (new FlowRouter)
                ->on('INIT', null, fn (array $payload): array => ['screen' => 'WELCOME', 'data' => $payload['data'] ?? []]);
        }

        protected function getNextScreen(array $decryptedBody): array
        {
            return ['screen' => 'FALLBACK'];
        }
    };

    $method = new ReflectionMethod($controller, 'resolveFlowResponse');

    $payload = $method->invoke($controller, ['action' => 'INIT', 'screen' => 'START', 'data' => ['name' => 'Neo']]);

    expect($payload)->toBe(['screen' => 'WELCOME', 'data' => ['name' => 'Neo']]);
});

it('validates expected flow tokens', function (): void {
    $controller = new class(resolve(FlowCrypto::class), resolve(FlowTokenValidator::class)) extends FlowEndpointController
    {
        protected function expectedFlowToken(): string
        {
            return 'token-123';
        }

        protected function getNextScreen(array $decryptedBody): array
        {
            return ['screen' => 'OK'];
        }
    };

    $method = new ReflectionMethod($controller, 'validateFlowToken');

    expect(fn (): mixed => $method->invoke($controller, ['flow_token' => 'wrong-token']))->toThrow(FlowTokenException::class);
});
