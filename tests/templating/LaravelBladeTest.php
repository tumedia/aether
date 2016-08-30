<?php

class SmartyIntegratesWithAetherTest extends PHPUnit_Framework_TestCase
{
    public function testLaravelBladeEngineFactory()
    {
        $sl = new AetherServiceLocator;

        $tpl = AetherTemplate::get('AetherTemplateBlade');


    }
}
