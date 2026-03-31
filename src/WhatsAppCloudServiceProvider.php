<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud;

use Mohapinkepane\WhatsAppCloud\Client\WhatsAppClient;
use Mohapinkepane\WhatsAppCloud\Config\WhatsAppConfig;
use Mohapinkepane\WhatsAppCloud\Console\Commands\GenerateWhatsAppKeyPairCommand;
use Mohapinkepane\WhatsAppCloud\Console\Commands\PublishWhatsAppPublicKeyCommand;
use Mohapinkepane\WhatsAppCloud\Console\Commands\SyncConversationalComponentsCommand;
use Mohapinkepane\WhatsAppCloud\Contracts\SendsWhatsAppMessages;
use Mohapinkepane\WhatsAppCloud\Flows\FlowCrypto;
use Mohapinkepane\WhatsAppCloud\Flows\FlowTokenValidator;
use Mohapinkepane\WhatsAppCloud\Notifications\WhatsAppChannel;
use Mohapinkepane\WhatsAppCloud\Webhooks\WebhookRequestParser;
use Mohapinkepane\WhatsAppCloud\Webhooks\WebhookSignatureValidator;
use Spatie\LaravelPackageTools\Package;
use Spatie\LaravelPackageTools\PackageServiceProvider;

final class WhatsAppCloudServiceProvider extends PackageServiceProvider
{
    public function configurePackage(Package $package): void
    {
        $package
            ->name('whatsapp-cloud')
            ->hasConfigFile('whatsapp-cloud')
            ->hasCommands([
                GenerateWhatsAppKeyPairCommand::class,
                PublishWhatsAppPublicKeyCommand::class,
                SyncConversationalComponentsCommand::class,
            ]);
    }

    public function packageRegistered(): void
    {
        $this->app->singleton(WhatsAppConfig::class, fn ($app): WhatsAppConfig => WhatsAppConfig::fromArray(
            $app['config']->get('whatsapp-cloud', [])
        ));

        $this->app->singleton(WhatsAppClient::class);
        $this->app->alias(WhatsAppClient::class, SendsWhatsAppMessages::class);
        $this->app->singleton(WhatsAppChannel::class);
        $this->app->singleton(FlowCrypto::class);
        $this->app->singleton(FlowTokenValidator::class);
        $this->app->singleton(WebhookRequestParser::class);
        $this->app->singleton(WebhookSignatureValidator::class);
    }
}
