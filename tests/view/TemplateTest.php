<?php

class AetherTemplateTest extends PHPUnit_Framework_TestCase
{
    private function makeServiceLocator()
    {
        $sl = new AetherServiceLocator;
        $sl->set('projectRoot', __DIR__.'/');

        $config = new AetherConfig('./aether.config.xml');
        $sl->set('aetherConfig', $config);

        return $sl;
    }

    public function testGetTemplateObject()
    {
        $view = $this->makeServiceLocator()->getTemplate();
        $this->assertTrue($view instanceof AetherViewFactory);

        $view = $this->makeServiceLocator()->view();
        $this->assertTrue($view instanceof AetherViewFactory);
    }

    public function testAssignMethods()
    {
        $view = $this->makeServiceLocator()->getTemplate();

        $view->set('foo', 'bar123');
        $this->assertEquals((string)$view->fetch('test.tpl'), "bar123\n");

        $view->set('foo', 'bar123');
        $this->assertEquals((string)$view->fetch('blade'), "bar123\n");
    }

    /**
     * @todo more tests?
     */
}
