<?php

namespace Tests\Assets;

use Mockery as m;
use Tests\TestCase;
use Aether\Assets\AssetResolver;
use Illuminate\Filesystem\Filesystem;
use Mockery\Adapter\Phpunit\MockeryPHPUnitIntegration;

class AssetResolverTest extends TestCase
{
    use MockeryPHPUnitIntegration;

    public function testItFindsStaticAssetsURIs()
    {
        $files = m::mock(Filesystem::class);
        $files->shouldReceive('exists')->with('/tmp/assets/main.js')->andReturn(true);
        $files->shouldReceive('lastModified')->with('/tmp/assets/main.js')->andReturn(1337);

        $resolver = new AssetResolver($files, 'https://static/assets', '/tmp/assets');

        $this->assertEquals('https://static/assets/main.js?1337', $resolver->find('main.js'));
    }

    public function testItResolvesToSameOriginURIs()
    {
        $files = m::mock(Filesystem::class);
        $files->shouldReceive('exists')->with('/tmp/assets/main.js')->andReturn(true);
        $files->shouldReceive('lastModified')->with('/tmp/assets/main.js')->andReturn(1337);

        $resolver = new AssetResolver($files, '/assets', '/tmp/assets');

        $this->assertEquals('/assets/main.js?1337', $resolver->find('main.js'));
    }

    /**
     * @expectedException \Aether\Assets\AssetNotFoundException
     */
    public function testItThrowsExceptionIfTheAssetDoesNotExist()
    {
        $files = m::mock(Filesystem::class);
        $files->shouldReceive('exists')->with('/tmp/assets/main.js')->andReturn(false);

        $resolver = new AssetResolver($files, '/', '/tmp/assets');

        $resolver->find('main.js');
    }

    public function testTheFindAssetHelperFunction()
    {
        $this->assertRegExp(
            '/^\/assets\/main\.js\?[0-9]+$/',
            \find_asset('main.js')
        );
    }
}
