<?php
/**
 * Created by PhpStorm.
 * User: yury
 * Date: 3/12/16
 * Time: 6:44 PM
 */

namespace ApiBundle\Tests\Service\Routing;

use ApiBundle\Service\Routing\AutomaticClassLoader as ClassLoader;
use Symfony\Component\Routing\RouteCollection;
use ApiBundle\Controller\DefaultController;

class AutomaticClassLoaderTest extends \PHPUnit_Framework_TestCase {
    /**
     * @var ClassLoader
     */
    private $loader;

    public function setUp() {
        $this->loader = new ClassLoader();
    }

    public function testConstruct() {
        $this->assertInstanceOf(ClassLoader::class, $this->loader);
    }

    public function testSupports() {
        $this->assertTrue($this->loader->supports(null, ClassLoader::LOADER_NAME));
        $this->assertFalse($this->loader->supports(null, 'annotaiotion'));
    }

    public function testLoad() {
        $result = $this->loader->load(DefaultController::class);
        $this->assertInstanceOf(RouteCollection::class, $result);
    }

    public function testLoadClassNotExists() {
        $this->setExpectedException(\InvalidArgumentException::class);

        $resource = dirname(dirname(__DIR__)).'/Fixtures/Controller/DefaultController.php';
        $result   = $this->loader->load('ApiBundle\Tests\Fixtures\Controller\MissingController');
    }
}
