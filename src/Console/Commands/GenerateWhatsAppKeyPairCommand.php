<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Console\Commands;

use Illuminate\Console\Command;
use phpseclib3\Crypt\RSA;
use RuntimeException;

final class GenerateWhatsAppKeyPairCommand extends Command
{
    protected $signature = 'whatsapp:generate-key-pair {passphrase}';

    protected $description = 'Generate a WhatsApp Flows RSA public/private key pair.';

    public function handle(): int
    {
        $passphrase = (string) $this->argument('passphrase');

        $key = RSA::createKey(2048);
        $privateKey = $key->withPassword($passphrase)->toString('PKCS1');
        $publicKey = $key->getPublicKey()->toString('PKCS8');

        if ($privateKey === '' || $publicKey === '') {
            throw new RuntimeException('Unable to generate the RSA key pair.');
        }

        $this->info('Copy these values into your environment file:');
        $this->newLine();
        $this->line(sprintf('WHATSAPP_KEYS_PASSPHRASE="%s"', $passphrase));
        $this->line(sprintf('WHATSAPP_PUBLIC_KEY="%s"', $publicKey));
        $this->line(sprintf('WHATSAPP_PRIVATE_KEY="%s"', $privateKey));

        return self::SUCCESS;
    }
}
