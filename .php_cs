<?php

$finder = PhpCsFixer\Finder::create()->in(__DIR__);

/**
 * @see http://cs.sensiolabs.org/
 */
return PhpCsFixer\Config::create()->setFinder($finder)->setRules([
    '@PSR2' => true,
]);
