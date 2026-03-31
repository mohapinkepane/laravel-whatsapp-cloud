<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Tests;

use Mohapinkepane\WhatsAppCloud\Tests\Integration\Support\RealWhatsAppTestConfig;

abstract class IntegrationTestCase extends TestCase
{
    protected function defineEnvironment($app): void
    {
        parent::defineEnvironment($app);

        $config = RealWhatsAppTestConfig::fromEnvironment();

        if ($config->accessToken() !== null) {
            $app['config']->set('whatsapp-cloud.access_token', $config->accessToken());
        }

        if ($config->phoneNumberId() !== null) {
            $app['config']->set('whatsapp-cloud.phone_number_id', $config->phoneNumberId());
            $app['config']->set('whatsapp-cloud.notifications.default_phone_number_id', $config->phoneNumberId());
        }

        if ($config->graphBaseUrl() !== null) {
            $app['config']->set('whatsapp-cloud.graph_base_url', $config->graphBaseUrl());
        }

        if ($config->graphApiVersion() !== null) {
            $app['config']->set('whatsapp-cloud.graph_api_version', $config->graphApiVersion());
        }

        if ($config->httpTimeout() !== null) {
            $app['config']->set('whatsapp-cloud.http.timeout', $config->httpTimeout());
        }

        if ($config->retryTimes() !== null) {
            $app['config']->set('whatsapp-cloud.http.retry_times', $config->retryTimes());
        }

        if ($config->retrySleepMilliseconds() !== null) {
            $app['config']->set('whatsapp-cloud.http.retry_sleep_milliseconds', $config->retrySleepMilliseconds());
        }
    }
}
