<?php

class AetherServiceSentry extends AetherService
{
    public function register()
    {
        if (!config('app.sentry.enabled', false)) {
            return;
        }

        $client = $this->getClient();

        if (isset($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            $client->user_context([
                'ip_address' => $_SERVER['HTTP_X_FORWARDED_FOR'],
            ]);
        }

        $client->install();
    }

    protected function getClient()
    {
        return new Raven_Client(config('app.sentry.dsn'), [
            'trace' => true,
            'curl_method' => 'sync',
            'curl_ipv4' => false,
            'trust_x_forwarded_proto' => true,
            'tags' => [
                'php_version' => phpversion(),
            ],
        ]);
    }
}
