<?php

namespace Aether\Bootstrap;

use Aether\Aether;
use Aether\UrlParser;

class SetRequestForConsole
{
    public function bootstrap(Aether $aether)
    {
        $aether->singleton('request', function () {
            // @todo:
        });

        // $aether->singleton('parsedUrl', function () {
        //     return UrlParser::createFromGlobals();
        // });
    }
}
