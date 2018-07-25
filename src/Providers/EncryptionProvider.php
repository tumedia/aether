<?php

namespace Aether\Providers;

use RuntimeException;
use Illuminate\Support\Str;
use Illuminate\Encryption\Encrypter;

class EncryptionProvider extends Provider
{
    public function register()
    {
        $this->aether->singleton('encrypter', function ($aether) {
            if (Str::startsWith($key = $this->getKey($aether), 'base64:')) {
                $key = base64_decode(substr($key, 7));
            }

            return new Encrypter($key, 'AES-256-CBC');
        });
    }

    protected function getKey($aether)
    {
        $key = $aether['config']['app.key'];

        if (empty($key)) {
            throw new RuntimeException(
                'No application encryption key has been specified.'
            );
        }

        return $key;
    }
}
