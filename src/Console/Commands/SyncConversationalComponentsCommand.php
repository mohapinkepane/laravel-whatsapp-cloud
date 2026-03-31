<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Console\Commands;

use Illuminate\Console\Command;
use Mohapinkepane\WhatsAppCloud\Client\WhatsAppClient;

final class SyncConversationalComponentsCommand extends Command
{
    protected $signature = 'whatsapp:sync-conversational-components {--phone-number-id=}';

    protected $description = 'Sync configured WhatsApp conversational components to Meta.';

    public function __construct(private readonly WhatsAppClient $client)
    {
        parent::__construct();
    }

    public function handle(): int
    {
        $phoneNumberId = $this->option('phone-number-id');
        $this->client->syncConversationalComponents(is_string($phoneNumberId) ? $phoneNumberId : null);

        $this->info('Conversational components synced successfully.');

        return self::SUCCESS;
    }
}
