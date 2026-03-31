<?php

declare(strict_types=1);

use Mohapinkepane\WhatsAppCloud\Components\FlowActionPayload;
use Mohapinkepane\WhatsAppCloud\Contacts\Address;
use Mohapinkepane\WhatsAppCloud\Contacts\Contact;
use Mohapinkepane\WhatsAppCloud\Contacts\Email;
use Mohapinkepane\WhatsAppCloud\Contacts\Name;
use Mohapinkepane\WhatsAppCloud\Contacts\Organization;
use Mohapinkepane\WhatsAppCloud\Contacts\Phone;
use Mohapinkepane\WhatsAppCloud\Contacts\Url;
use Mohapinkepane\WhatsAppCloud\Messages\CallToActionUrlMessage;
use Mohapinkepane\WhatsAppCloud\Messages\ContactsMessage;
use Mohapinkepane\WhatsAppCloud\Messages\FlowMessage;

it('serializes contacts messages', function (): void {
    $contact = Contact::create(
        [Address::create('Menlo Park', 'United States')],
        '2012-08-18',
        [Email::create('test@example.com')],
        Name::create('John', 'John Smith', 'Smith'),
        Organization::create('WhatsApp', 'Manager'),
        [Phone::create('+1 (940) 555-1234', 'WORK', '16505551234')],
        [Url::create('https://example.com')],
    );

    $payload = ContactsMessage::create([$contact])->toArray();

    expect($payload['type'])->toBe('contacts')
        ->and($payload['contacts'])->toHaveCount(1)
        ->and($payload['contacts'][0]['name']['formatted_name'])->toBe('John Smith');
});

it('serializes call to action url buttons', function (): void {
    $payload = CallToActionUrlMessage::create(
        'Learn more about our bot',
        'Visit us',
        'https://example.com',
    )->toArray();

    expect($payload['interactive']['type'])->toBe('cta_url')
        ->and($payload['interactive']['action']['parameters']['display_text'])->toBe('Visit us');
});

it('serializes flow messages', function (): void {
    $payload = FlowMessage::create(
        'flow-id',
        'flow-token',
        'Take survey',
        'How do you like the service?',
        'draft',
        'navigate',
    )->addActionPayload(
        FlowActionPayload::create('RECOMMEND', ['title' => 'hello'])
    )->toArray();

    expect($payload['interactive']['type'])->toBe('flow')
        ->and($payload['interactive']['action']['parameters']['flow_id'])->toBe('flow-id')
        ->and($payload['interactive']['action']['parameters']['flow_action_payload']['screen'])->toBe('RECOMMEND');
});
