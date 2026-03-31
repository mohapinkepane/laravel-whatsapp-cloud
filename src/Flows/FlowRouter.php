<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Flows;

use Closure;
use Mohapinkepane\WhatsAppCloud\Exceptions\FlowRoutingException;

final class FlowRouter
{
    /**
     * @var array<int, array{action: string|null, screen: string|null, handler: Closure(array<string, mixed>): array<string, mixed>}>
     */
    private array $routes = [];

    /**
     * @param  Closure(array<string, mixed>): array<string, mixed>  $handler
     */
    public function on(?string $action, ?string $screen, Closure $handler): self
    {
        $this->routes[] = [
            'action' => $action,
            'screen' => $screen,
            'handler' => $handler,
        ];

        return $this;
    }

    /**
     * @param  array<string, mixed>  $payload
     * @return array<string, mixed>
     */
    public function dispatch(array $payload): array
    {
        $action = is_string($payload['action'] ?? null) ? $payload['action'] : null;
        $screen = is_string($payload['screen'] ?? null) ? $payload['screen'] : null;

        foreach ($this->routes as $route) {
            if (($route['action'] === null || $route['action'] === $action)
                && ($route['screen'] === null || $route['screen'] === $screen)) {
                return ($route['handler'])($payload);
            }
        }

        throw FlowRoutingException::routeNotFound($action, $screen);
    }
}
