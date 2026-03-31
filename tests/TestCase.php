<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Tests;

use Mohapinkepane\WhatsAppCloud\WhatsAppCloudServiceProvider;
use Orchestra\Testbench\TestCase as Orchestra;

abstract class TestCase extends Orchestra
{
    /**
     * @return array<int, class-string>
     */
    protected function getPackageProviders($app): array
    {
        return [WhatsAppCloudServiceProvider::class];
    }

    protected function defineEnvironment($app): void
    {
        $app['config']->set('whatsapp-cloud.access_token', 'test-token');
        $app['config']->set('whatsapp-cloud.phone_number_id', '123456789');
        $app['config']->set('whatsapp-cloud.notifications.default_phone_number_id', '123456789');
        $app['config']->set('whatsapp-cloud.webhook.verify_token', 'verify-me');
        $app['config']->set('whatsapp-cloud.webhook.app_secret', 'app-secret');
        $app['config']->set('whatsapp-cloud.webhook.restrict_inbound_messages_to_phone_number_id', true);
        $app['config']->set('whatsapp-cloud.flow.message_version', '3');
        $app['config']->set('whatsapp-cloud.conversational_components', [
            'enable_welcome_message' => true,
            'commands' => [
                ['command_name' => 'help', 'command_description' => 'Get help'],
            ],
            'prompts' => ['Book a flight'],
        ]);
        $app['config']->set('whatsapp-cloud.strict_mode', true);
    }
}
