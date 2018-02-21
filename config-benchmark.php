<?php

use Aether\Aether;
use Aether\UrlParser;

require_once __DIR__.'/vendor/autoload.php';

function benchmark($name, callable $function, $iterations = 2000) {
    $start = microtime(true);

    for ($i = 0; $i < $iterations; $i++) {
        $function();
    }

    $timeInMs = microtime(true) - $start;

    echo "Ran the benchmark \"{$name}\" {$iterations} times in {$timeInMs} s\n";
}

function getAether() {
    $aether = new Aether(__DIR__.'/tests/Fixtures');

    $aether->singleton('parsedUrl', function () {
        $aetherUrl = new UrlParser;

        $aetherUrl->parse('http://raw.no/unittest');

        return $aetherUrl;
    });

    $aetherConfig = $aether['aetherConfig'];

    $aetherConfig->matchUrl($aether['parsedUrl']);

    return $aether;
}

benchmark('Without autogenerated.xml', function () {
    $aether = getAether();

    $options = $aether['aetherConfig']->getOptions();

    if ($options['nestedImportedOption3'] !== 'yup') {
        exit(1);
    }
});

// Prepare autogenerated.xml for the next benchmark...
$aether = getAether();
$aether['aetherConfig']->saveToFile(
    $autogeneratedPath = "{$aether['projectRoot']}config/autogenerated.config.xml"
);

file_put_contents(
    $configFile = "{$aether['projectRoot']}config/compiled.php",
    '<?php return '.var_export($aether['config']->all(), true).';'
);

benchmark('With autogenerated.xml', function () {
    $aether = getAether();

    $options = $aether['aetherConfig']->getOptions();

    if ($options['nestedImportedOption3'] !== 'yup') {
        exit(1);
    }
});

unlink($autogeneratedPath);
unlink($configFile);
