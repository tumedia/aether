<?php

namespace Aether\Providers;

use Raven_Client;

class SentryProvider extends Provider
{
    public function register()
    {
        $this->aether->singleton('sentry.client', function ($aether) {
            return $this->getClient($aether);
        });
    }

    protected function getClient($aether)
    {
        $projectRoot = rtrim($aether['projectRoot'], '/');

        return new Raven_Client(config('app.sentry.dsn'), [
            'environment' => config('app.env'),
            'app_path' => $projectRoot,
            'prefixes' => [$projectRoot],
            'excluded_app_paths' => ["{$projectRoot}/vendor"],
            'trace' => true,
            'curl_method' => 'sync',
            'curl_ipv4' => false,
            'trust_x_forwarded_proto' => true,
            'tags' => [
                'php_version' => phpversion(),
                'project_root' => $projectRoot,
            ],
        ]);
    }
}
