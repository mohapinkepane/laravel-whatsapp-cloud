<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Webhooks;

use Mohapinkepane\WhatsAppCloud\Config\WhatsAppConfig;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingMessage;
use Mohapinkepane\WhatsAppCloud\Inbound\IncomingUser;

final readonly class WebhookRequestParser
{
    public function __construct(private ?WhatsAppConfig $config = null) {}

    /**
     * @param  array<string, mixed>  $payload
     */
    public function parse(array $payload): WebhookPayload
    {
        $messages = [];
        $statuses = [];

        foreach ($payload['entry'] ?? [] as $entry) {
            if (! is_array($entry)) {
                continue;
            }

            foreach ($entry['changes'] ?? [] as $change) {
                if (! is_array($change)) {
                    continue;
                }

                $value = $change['value'] ?? null;

                if (! is_array($value)) {
                    continue;
                }

                if (! $this->shouldProcessValue($value)) {
                    continue;
                }

                $metadata = is_array($value['metadata'] ?? null) ? $value['metadata'] : [];
                $contacts = is_array($value['contacts'] ?? null) ? $value['contacts'] : [];

                foreach ($value['messages'] ?? [] as $message) {
                    if (! is_array($message)) {
                        continue;
                    }

                    $contact = $this->findMatchingContact($contacts, $message);
                    $user = IncomingUser::fromWebhook($message, $contact);
                    $type = is_string($message['type'] ?? null) ? $message['type'] : 'unknown';
                    $id = is_string($message['id'] ?? null) ? $message['id'] : '';

                    $messages[] = new IncomingMessage($id, $type, $user, $message, $metadata);
                }

                foreach ($value['statuses'] ?? [] as $status) {
                    if (! is_array($status)) {
                        continue;
                    }

                    $conversation = is_array($status['conversation'] ?? null) ? $status['conversation'] : [];
                    $origin = is_array($conversation['origin'] ?? null) ? $conversation['origin'] : [];
                    $pricing = is_array($status['pricing'] ?? null) ? $status['pricing'] : [];
                    $errors = is_array($status['errors'] ?? null) ? $status['errors'] : [];

                    $statuses[] = new WebhookStatus(
                        is_string($status['id'] ?? null) ? $status['id'] : '',
                        is_string($status['status'] ?? null) ? $status['status'] : 'unknown',
                        is_string($status['recipient_id'] ?? null) ? $status['recipient_id'] : null,
                        is_string($conversation['id'] ?? null) ? $conversation['id'] : null,
                        is_string($origin['type'] ?? null) ? $origin['type'] : null,
                        is_string($pricing['category'] ?? null) ? $pricing['category'] : null,
                        is_bool($pricing['billable'] ?? null) ? $pricing['billable'] : null,
                        array_values(array_filter(array_map(
                            static fn (mixed $error): ?WebhookError => is_array($error) ? WebhookError::fromArray($error) : null,
                            $errors,
                        ))),
                        $status,
                    );
                }
            }
        }

        return new WebhookPayload($messages, $statuses, $payload);
    }

    /**
     * @param  array<int, mixed>  $contacts
     * @param  array<string, mixed>  $message
     * @return array<string, mixed>|null
     */
    private function findMatchingContact(array $contacts, array $message): ?array
    {
        $from = $message['from'] ?? null;

        foreach ($contacts as $contact) {
            if (! is_array($contact)) {
                continue;
            }

            if (($contact['wa_id'] ?? null) === $from) {
                return $contact;
            }
        }

        return null;
    }

    /**
     * @param  array<string, mixed>  $value
     */
    private function shouldProcessValue(array $value): bool
    {
        if (! $this->config instanceof WhatsAppConfig || ! $this->config->restrictInboundMessagesToPhoneNumberId()) {
            return true;
        }

        $configuredPhoneNumberId = $this->config->phoneNumberId();

        if ($configuredPhoneNumberId === null) {
            return true;
        }

        $metadata = $value['metadata'] ?? null;
        $phoneNumberId = is_array($metadata) && is_string($metadata['phone_number_id'] ?? null)
            ? $metadata['phone_number_id']
            : null;

        return $phoneNumberId === $configuredPhoneNumberId;
    }
}
