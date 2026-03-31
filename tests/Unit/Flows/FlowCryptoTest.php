<?php

declare(strict_types=1);

use Illuminate\Http\Request;
use Mohapinkepane\WhatsAppCloud\Flows\FlowCrypto;
use phpseclib3\Crypt\AES;
use phpseclib3\Crypt\RSA;

it('decrypts flow requests and encrypts responses', function (): void {
    $keyPair = RSA::createKey(2048);
    $passphrase = 'test-passphrase';
    $privateKey = $keyPair->withPassword($passphrase)->toString('PKCS1');
    $publicKey = $keyPair->getPublicKey();
    $aesKey = random_bytes(16);
    $iv = random_bytes(12);

    config()->set('whatsapp-cloud.flow.private_key', $privateKey);
    config()->set('whatsapp-cloud.flow.passphrase', $passphrase);
    config()->set('whatsapp-cloud.flow.public_key', $publicKey->toString('PKCS8'));

    $body = ['screen' => 'INIT', 'data' => ['name' => 'Neo']];
    $json = json_encode($body, JSON_THROW_ON_ERROR);

    $aes = new AES('gcm');
    $aes->setKey($aesKey);
    $aes->setNonce($iv);

    $cipher = $aes->encrypt($json);
    $tag = $aes->getTag();

    $request = Request::create('/flow', 'POST', server: ['CONTENT_TYPE' => 'application/json'], content: json_encode([
        'encrypted_aes_key' => base64_encode(
            (string) (
                $publicKey
                    ->withPadding(RSA::ENCRYPTION_OAEP)
                    ->withHash('sha256')
                    ->withMGFHash('sha256')
                    ->encrypt($aesKey)
            )
        ),
        'encrypted_flow_data' => base64_encode($cipher.$tag),
        'initial_vector' => base64_encode($iv),
    ], JSON_THROW_ON_ERROR));

    $crypto = resolve(FlowCrypto::class);
    $flowRequest = $crypto->decryptRequest($request);
    $encryptedResponse = $crypto->encryptResponse(['screen' => 'DONE'], $flowRequest->aesKey(), $flowRequest->initialVector());

    $flippedIv = $flowRequest->initialVector() ^ str_repeat("\xFF", strlen($flowRequest->initialVector()));
    $decodedResponse = base64_decode($encryptedResponse, true);
    $responseCipher = substr($decodedResponse, 0, -16);
    $responseTag = substr($decodedResponse, -16);
    $responseAes = new AES('gcm');
    $responseAes->setKey($flowRequest->aesKey());
    $responseAes->setNonce($flippedIv);
    $responseAes->setTag($responseTag);

    $decryptedResponse = $responseAes->decrypt($responseCipher);

    expect($flowRequest->body())->toBe($body)
        ->and(json_decode($decryptedResponse, true, flags: JSON_THROW_ON_ERROR))->toBe(['screen' => 'DONE']);
});
