<?php

declare(strict_types=1);

namespace Mohapinkepane\WhatsAppCloud\Flows;

use Illuminate\Http\Request;
use Mohapinkepane\WhatsAppCloud\Config\WhatsAppConfig;
use Mohapinkepane\WhatsAppCloud\Exceptions\FlowCryptoException;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\PublicKeyLoader;
use phpseclib3\Crypt\RSA;
use phpseclib3\Crypt\RSA\PrivateKey as RsaPrivateKey;

final readonly class FlowCrypto
{
    public function __construct(private WhatsAppConfig $config) {}

    public function decryptRequest(Request $request): FlowRequest
    {
        $privateKey = $this->config->flowPrivateKey();
        $passphrase = $this->config->flowPassphrase();

        if ($privateKey === null) {
            throw FlowCryptoException::missingKey('whatsapp-cloud.flow.private_key');
        }

        if ($passphrase === null) {
            throw FlowCryptoException::missingKey('whatsapp-cloud.flow.passphrase');
        }

        /** @var array<string, string> $body */
        $body = $request->json()->all();
        $encryptedAesKey = base64_decode((string) ($body['encrypted_aes_key'] ?? ''), true);
        $encryptedFlowData = base64_decode((string) ($body['encrypted_flow_data'] ?? ''), true);
        $initialVector = base64_decode((string) ($body['initial_vector'] ?? ''), true);

        if ($encryptedAesKey === false || $encryptedFlowData === false || $initialVector === false) {
            throw FlowCryptoException::decryptionFailed('The flow request payload is not valid base64 data.');
        }

        $rsa = $this->loadPrivateRsaKey($privateKey, $passphrase)
            ->withPadding(RSA::ENCRYPTION_OAEP)
            ->withHash('sha256')
            ->withMGFHash('sha256');

        $decryptedAesKey = $rsa->decrypt($encryptedAesKey);

        if ($decryptedAesKey === '') {
            throw FlowCryptoException::decryptionFailed('Decryption of the AES key failed.');
        }

        $tagLength = 16;
        $ciphertext = substr($encryptedFlowData, 0, -$tagLength);
        $tag = substr($encryptedFlowData, -$tagLength);
        $aes = new AES('gcm');
        $aes->setKey($decryptedAesKey);
        $aes->setNonce($initialVector);
        $aes->setTag($tag);

        $decrypted = $aes->decrypt($ciphertext);

        if ($decrypted === '') {
            throw FlowCryptoException::decryptionFailed('Decryption of the flow data failed.');
        }

        $decoded = json_decode($decrypted, true);

        if (! is_array($decoded)) {
            throw FlowCryptoException::decryptionFailed('Decrypted flow data is not valid JSON.');
        }

        return new FlowRequest($decoded, $decryptedAesKey, $initialVector);
    }

    /**
     * @param  array<string, mixed>  $payload
     */
    public function encryptResponse(array $payload, string $aesKey, string $initialVector): string
    {
        $flippedIv = $initialVector ^ str_repeat("\xFF", strlen($initialVector));
        $json = json_encode($payload, JSON_THROW_ON_ERROR);
        $aes = new AES('gcm');
        $aes->setKey($aesKey);
        $aes->setNonce($flippedIv);

        $cipher = $aes->encrypt($json);
        $tag = $aes->getTag();

        if ($cipher === '' || $tag === '') {
            throw FlowCryptoException::encryptionFailed('Encryption of the flow response failed.');
        }

        return base64_encode($cipher.$tag);
    }

    private function loadPrivateRsaKey(string $privateKey, string $passphrase): RsaPrivateKey
    {
        $key = PublicKeyLoader::loadPrivateKey($privateKey, $passphrase);

        if (! $key instanceof RsaPrivateKey) {
            throw FlowCryptoException::decryptionFailed('The WhatsApp flow private key is not a valid RSA private key.');
        }

        return $key;
    }
}
