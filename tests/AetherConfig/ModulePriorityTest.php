<?php

namespace Tests\AetherConfig;

use PHPUnit\Framework\Assert;

class ModulePriorityTest extends AbstractAetherConfigTest
{
    public function testModuleWithProvidesAttributeWinsToOneThatDoesnt()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <module>FooModule</module>
                <module provides="foo">FooModule</module>
            </rule>')
            ->andUrl('http://test/foo')
            ->assertModuleOrder(['foo', 'FooModule']);
    }

    public function testModulePriorityAttribute()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <module priority="-10">FooModule</module>
                <module provides="foo">FooModule</module>
                <module priority="20" provides="twenty">FooModule</module>
                <module priority="10" provides="ten">FooModule</module>
                <module provides="other">FooModule</module>
            </rule>')
            ->andUrl('http://test/foo')
            ->assertModuleOrder(['FooModule', 'foo', 'other', 'ten', 'twenty']);
    }

    protected function assertModuleOrder(array $expected)
    {
        $actual = array_keys($this->aetherConfig->getModules());

        Assert::assertEquals(
            $expected,
            $actual,
            'Modules are not ordered correctly. Expected ['.implode(', ', $expected).'], but got ['.implode(', ', $actual).']'
        );

        return $this;
    }
}
