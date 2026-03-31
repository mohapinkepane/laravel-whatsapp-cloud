<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Client;

use Closure;
use GuzzleHttp\Psr7\Response as PsrResponse;
use Illuminate\Http\Client\Response;
use LogicException;
use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Exceptions\ValidationException;

final class PendingSendResponse extends Response
{
    private bool $sent = false;

    /**
     * @param  Closure(array<string, mixed>|ProvidesWhatsAppPayload): Response  $dispatch
     * @param  array<string, mixed>|ProvidesWhatsAppPayload  $message
     */
    public function __construct(
        private readonly Closure $dispatch,
        private array|ProvidesWhatsAppPayload $message,
    ) {
        parent::__construct(new PsrResponse(200, [], '{}'));
    }

    public function __destruct()
    {
        if ($this->hasBeenSent()) {
            return;
        }

        $this->dispatchIfNeeded();
    }

    public function replyTo(string $messageId): self
    {
        if ($this->hasBeenSent()) {
            throw new LogicException('Cannot call replyTo() after the message has already been sent.');
        }

        $this->message = $this->withContextMessageId($messageId);

        $this->dispatchIfNeeded();

        return $this;
    }

    public function send(): self
    {
        $this->dispatchIfNeeded();

        return $this;
    }

    public function body()
    {
        $this->dispatchIfNeeded();

        return parent::body();
    }

    public function header(string $header)
    {
        $this->dispatchIfNeeded();

        return parent::header($header);
    }

    /**
     * @return array<string, array<int, string>>
     */
    public function headers()
    {
        $this->dispatchIfNeeded();

        return parent::headers();
    }

    public function status()
    {
        $this->dispatchIfNeeded();

        return parent::status();
    }

    public function reason()
    {
        $this->dispatchIfNeeded();

        return parent::reason();
    }

    public function resource()
    {
        $this->dispatchIfNeeded();

        return parent::resource();
    }

    public function close()
    {
        $this->dispatchIfNeeded();

        return parent::close();
    }

    public function toPsrResponse()
    {
        $this->dispatchIfNeeded();

        return parent::toPsrResponse();
    }

    public function effectiveUri()
    {
        $this->dispatchIfNeeded();

        return parent::effectiveUri();
    }

    public function cookies()
    {
        $this->dispatchIfNeeded();

        return parent::cookies();
    }

    /**
     * @return array<string, mixed>
     */
    public function handlerStats()
    {
        $this->dispatchIfNeeded();

        return parent::handlerStats();
    }

    public function offsetExists($offset): bool
    {
        $this->dispatchIfNeeded();

        return parent::offsetExists($offset);
    }

    public function offsetGet($offset): mixed
    {
        $this->dispatchIfNeeded();

        return parent::offsetGet($offset);
    }

    public function __toString()
    {
        $this->dispatchIfNeeded();

        return parent::__toString();
    }

    /**
     * @param  array<int, mixed>  $parameters
     */
    public function __call($method, $parameters)
    {
        $this->dispatchIfNeeded();

        return parent::__call($method, $parameters);
    }

    private function dispatchIfNeeded(): void
    {
        if ($this->hasBeenSent()) {
            return;
        }

        $response = ($this->dispatch)($this->message);

        $this->response = $response->toPsrResponse();
        $this->cookies = $response->cookies;
        $this->transferStats = $response->transferStats;
        $this->sent = true;
    }

    private function hasBeenSent(): bool
    {
        return $this->sent;
    }

    /**
     * @return array<string, mixed>|ProvidesWhatsAppPayload
     */
    private function withContextMessageId(string $messageId): array|ProvidesWhatsAppPayload
    {
        if (is_array($this->message)) {
            return array_merge($this->message, [
                'context' => [
                    'message_id' => $messageId,
                ],
            ]);
        }

        if (method_exists($this->message, 'contextMessageId')) {
            return $this->message->contextMessageId($messageId);
        }

        throw ValidationException::invalidMessage('replyTo() requires a payload array or a context-aware message builder.');
    }
}
