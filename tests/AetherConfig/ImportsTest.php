<?php

namespace Tests\AetherConfig;

class ImportsTest extends AbstractAetherConfigTest
{
    public function testItImportsImportNodes()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <option name="foo">bar</option>
                <import>a</import>
            </rule>')
            ->withConfigFile('a.xml', '
            <option name="importedOption">yup</option>
            <import>b</import>')
            ->withoutConfigFile('prod.a.xml')
            ->withoutConfigFile('test.a.xml')
            ->withConfigFile('b.xml', '
            <option name="nestedImportedOption">hell yeah</option>')
            ->withoutConfigFile('prod.b.xml')
            ->withoutConfigFile('test.b.xml')
            ->andUrl('http://test/foo')
            ->assertOption('foo', 'bar')
            ->assertOption('importedOption', 'yup')
            ->assertOption('nestedImportedOption', 'hell yeah');
    }

    public function testItImportsImportNodesBasedOnLocalEnvironment()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <import>a</import>
            </rule>')
            ->withConfigFile('a.xml', '<option name="foo">a-base</option>')
            ->withConfigFile('test.a.xml', '<option name="foo">test-a</option>')
            ->withConfigFile('prod.a.xml', '<option name="foo">prod-a</option>')
            ->andUrl('http://test/foo')
            ->assertOption('foo', 'test-a');
    }

    public function testItImportsTheUnprefixedFileInProduction()
    {
        config()->set('app.env', 'production');

        $this->givenUrlRules('
            <rule match="foo">
                <import>a</import>
            </rule>')
            ->withConfigFile('a.xml', '<option name="foo">a-base</option>')
            ->withConfigFile('test.a.xml', '<option name="foo">test-a</option>')
            ->withConfigFile('prod.a.xml', '<option name="foo">prod-a</option>')
            ->andUrl('http://test/foo')
            ->assertOption('foo', 'a-base');
    }

    public function testItImportsTheProdPrefixedFileInProductionWhenThereIsNoUnprefixedFile()
    {
        config()->set('app.env', 'production');

        $this->givenUrlRules('
            <rule match="foo">
                <import>a</import>
            </rule>')
            ->withoutConfigFile('a.xml')
            ->withConfigFile('test.a.xml', '<option name="foo">test-a</option>')
            ->withConfigFile('prod.a.xml', '<option name="foo">prod-a</option>')
            ->andUrl('http://test/foo')
            ->assertOption('foo', 'prod-a');
    }

    /**
     * @expectedException \Aether\Exceptions\MissingFile
     */
    public function testItThrowsWhenImportingAFileThatDoesntExist()
    {
        $this->givenUrlRules('
            <rule match="foo">
                <import>a</import>
            </rule>')
            ->withoutConfigFile('a.xml')
            ->withoutConfigFile('test.a.xml')
            ->withoutConfigFile('prod.a.xml')
            ->andUrl('http://test/foo');
    }
}
