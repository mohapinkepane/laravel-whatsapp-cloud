<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Contacts;

use Mohapinkepane\WhatsAppCloud\Contracts\ProvidesWhatsAppPayload;

final readonly class Contact implements ProvidesWhatsAppPayload
{
    /**
     * @param  array<int, Address>  $addresses
     * @param  array<int, Email>  $emails
     * @param  array<int, Phone>  $phones
     * @param  array<int, Url>  $urls
     */
    public function __construct(
        private array $addresses,
        private ?string $birthday,
        private array $emails,
        private Name $name,
        private ?Organization $organization,
        private array $phones,
        private array $urls,
    ) {}

    /**
     * @param  array<int, Address>  $addresses
     * @param  array<int, Email>  $emails
     * @param  array<int, Phone>  $phones
     * @param  array<int, Url>  $urls
     */
    public static function create(
        array $addresses,
        ?string $birthday,
        array $emails,
        Name $name,
        ?Organization $organization,
        array $phones,
        array $urls,
    ): self {
        return new self($addresses, $birthday, $emails, $name, $organization, $phones, $urls);
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(): array
    {
        return array_filter([
            'addresses' => $this->addresses === [] ? null : array_map(
                static fn (Address $address): array => $address->toArray(),
                $this->addresses,
            ),
            'birthday' => $this->birthday,
            'emails' => $this->emails === [] ? null : array_map(
                static fn (Email $email): array => $email->toArray(),
                $this->emails,
            ),
            'name' => $this->name->toArray(),
            'org' => $this->organization?->toArray(),
            'phones' => $this->phones === [] ? null : array_map(
                static fn (Phone $phone): array => $phone->toArray(),
                $this->phones,
            ),
            'urls' => $this->urls === [] ? null : array_map(
                static fn (Url $url): array => $url->toArray(),
                $this->urls,
            ),
        ], static fn (mixed $value): bool => $value !== null);
    }
}
