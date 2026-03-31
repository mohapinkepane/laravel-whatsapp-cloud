<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Console\Commands;

use Illuminate\Console\Command;
use Mohapinkepane\WhatsAppCloud\Client\WhatsAppClient;
use Mohapinkepane\WhatsAppCloud\Config\WhatsAppConfig;
use Mohapinkepane\WhatsAppCloud\Exceptions\ValidationException;

final class PublishWhatsAppPublicKeyCommand extends Command
{
    protected $signature = 'whatsapp:publish-public-key {--phone-number-id=}';

    protected $description = 'Publish the configured WhatsApp Flows public key to Meta.';

    public function __construct(
        private readonly WhatsAppClient $client,
        private readonly WhatsAppConfig $config,
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $publicKey = $this->config->flowPublicKey();
        $phoneNumberId = $this->option('phone-number-id') ?: $this->config->phoneNumberId();

        if ($publicKey === null) {
            throw ValidationException::missingConfiguration('whatsapp-cloud.flow.public_key');
        }

        if (! is_string($phoneNumberId) || $phoneNumberId === '') {
            throw ValidationException::missingConfiguration('whatsapp-cloud.phone_number_id');
        }

        $this->client->post(sprintf('%s/whatsapp_business_encryption', $phoneNumberId), [
            'business_public_key' => $publicKey,
        ]);

        $this->info('WhatsApp public key published successfully.');

        return self::SUCCESS;
    }
}
