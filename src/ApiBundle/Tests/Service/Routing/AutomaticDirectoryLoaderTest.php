<?php
/**
 * Created by PhpStorm.
 * User: yury
 * Date: 3/12/16
 * Time: 6:44 PM
 */

namespace ApiBundle\Tests\Service\Routing;

use ApiBundle\Service\Routing\AutomaticDirectoryLoader as Loader;
use ApiBundle\Service\Routing\AutomaticClassLoader as ClassLoader;
use Symfony\Component\Routing\RouteCollection;
use Symfony\Component\Config\FileLocatorInterface;

class AutomaticDirectoryLoaderTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var Loader
     */
    private $loader;

    /**
     * @var ClassLoader|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockClassLoader;

    /**
     * @var FileLocatorInterface|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockFileLocator;

    /**
     * @var RouteCollection|\PHPUnit_Framework_MockObject_MockObject
     */
    private $mockRouteCollection;

    public function setUp() {
        $this->mockClassLoader = $this->getMockBuilder(ClassLoader::class)
            ->disableOriginalConstructor()->getMock();
        $this->mockRouteCollection = $this->getMockBuilder(RouteCollection::class)
            ->disableOriginalConstructor()->getMock();
        $this->mockFileLocator = $this->getMock(FileLocatorInterface::class);

        $this->loader = new Loader($this->mockClassLoader, $this->mockFileLocator);
    }

    public function testConstruct() {
        $this->assertInstanceOf(Loader::class, $this->loader);
    }

    public function testSupports() {
        $this->assertTrue($this->loader->supports(null, Loader::LOADER_NAME));
        $this->assertFalse($this->loader->supports(null, 'annotaiotion'));
    }

    public function testLoad() {
        $dir = dirname(dirname(__DIR__)).'/Fixtures/Controller';

        $this->mockFileLocator->expects($this->once())
            ->method('locate')->willReturn($dir);

        $this->mockClassLoader->expects($this->any())
            ->method('load')->willReturn($this->mockRouteCollection);

        $this->mockRouteCollection->expects($this->any())
            ->method('all')->willReturn([]);

        $this->mockRouteCollection->expects($this->any())
            ->method('getResources')->willReturn([]);

        $result   = $this->loader->load('@ApiBundle/Controller');
        $this->assertInstanceOf(RouteCollection::class, $result);

        $this->setExpectedException(\RuntimeException::class, 'Do not add the "extra" loader twice');

        $this->loader->load($dir);
    }
}
