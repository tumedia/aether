<?php

return Tumedia\CS\Config::tap(function ($config) {
    $config->getFinder()->in(__DIR__);
});
