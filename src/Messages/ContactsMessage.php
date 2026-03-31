<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Messages;

use Mohapinkepane\WhatsAppCloud\Contacts\Contact;
use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;
use Mohapinkepane\WhatsAppCloud\Exceptions\ValidationException;
use Mohapinkepane\WhatsAppCloud\Messages\Concerns\HasContext;

final class ContactsMessage implements ProvidesWhatsAppPayload
{
    use HasContext;

    /**
     * @param  array<int, Contact>  $contacts
     */
    private function __construct(private readonly array $contacts) {}

    /**
     * @param  array<int, Contact>  $contacts
     */
    public static function create(array $contacts): self
    {
        return new self($contacts);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        if ($this->contacts === []) {
            throw ValidationException::invalidMessage('Contacts messages require at least one contact.');
        }

        return array_merge($this->buildContextPayload(), [
            'messaging_product' => 'whatsapp',
            'type' => 'contacts',
            'contacts' => array_map(
                static fn (Contact $contact): array => $contact->toArray(),
                $this->contacts,
            ),
        ]);
    }
}
