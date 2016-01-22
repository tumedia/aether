<?php

if (!defined('AETHER_PATH'))
    define('AETHER_PATH', __DIR__ . '/../');

require_once(AETHER_PATH . "Aether.php");
spl_autoload_register(array('Aether', 'autoLoad'));

