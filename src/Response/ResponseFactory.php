<?php

namespace Aether\Response;

use Aether\Aether;
use Aether\AetherConfig;
use Aether\Sections\Section;
use Aether\Modules\ModuleFactory;
use Aether\Exceptions\ServiceNotFound;

class ResponseFactory
{
    protected $requestedService;

    protected $requestedModule;

    protected $requestedEsi;

    public static function createFromGlobals()
    {
        return new static([
            'service'  => $_GET['service'] ?? null,
            'module'   => $_GET['module'] ?? null,
            'esi'      => $_GET['_esi'] ?? null,
        ]);
    }

    public function __construct(array $request)
    {
        $this->requestedService  = $request['service'];
        $this->requestedModule   = $request['module'];
        $this->requestedEsi      = $request['esi'];
    }

    public function getResponse(Section $section, AetherConfig $config): Response
    {
        if (! is_null($this->requestedService)) {
            return $section->service($this->requestedModule, $this->requestedService);
        }

        if ($this->requestedEsi === '') {
            return $this->listAvailableProviders($config);
        }

        if (! is_null($this->requestedEsi)) {
            return $this->getEsiResponse($section, $config);
        }

        return $this->getDefaultResponse($section, $config->getOptions());
    }

    protected function getDefaultResponse(Section $section, array $options)
    {
        if (isset($options['session']) && $options['session'] == 'on') {
            session_start();
        }

        return $section->response();
    }

    protected function getEsiResponse(Section $section, AetherConfig $config)
    {
        $module = $config->getModules($this->requestedEsi);

        if (! $module) {
            throw new ServiceNotFound("Provider [{$this->requestedEsi}] does not match any module");
        }

        $object = ModuleFactory::create(
            $module['name'],
            Aether::getInstance(),
            ($module['options'] ?? []) + $config->getOptions()
        );

        $maxAge = 0;

        if ($object->getCacheTime() !== null) {
            $maxAge = min($object->getCacheTime(), $maxAge);
        }

        if (isset($module['cache'])) {
            $maxAge = min($module['cache'], $maxAge);
        }

        if ($maxAge > 0) {
            header("Cache-Control: s-maxage={$maxAge}");
        }

        return new Text($object->run(), 'text/html');
    }

    protected function listAvailableProviders(AetherConfig $config)
    {
        $providers = [];

        foreach ($config->getModules() as $module) {
            $provider = [
                'provides' => $module['provides'] ?? null,
                'cache'    => $module['cache'] ?? false,
            ];

            if (isset($module['module'])) {
                $provider['providers'] = array_map(function ($module) {
                    return [
                        'provides' => $module['provides'],
                        'cache'    => $module['cache'] ?? false,
                    ];
                }, array_values($module['module']));
            }

            $providers[] = $provider;
        }

        return new Json(compact('providers'));
    }
}
