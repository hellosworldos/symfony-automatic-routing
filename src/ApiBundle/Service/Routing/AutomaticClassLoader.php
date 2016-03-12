<?php
/**
 * Created by PhpStorm.
 * User: yury
 * Date: 3/12/16
 * Time: 6:51 PM
 */

namespace ApiBundle\Service\Routing;

use Symfony\Component\Config\Loader\Loader as SymfonyLoader;
use Symfony\Component\Routing\RouteCollection;
use InvalidArgumentException;
use ReflectionClass;
use ReflectionMethod;
use Symfony\Component\Routing\Route;

class AutomaticClassLoader extends SymfonyLoader {
    const LOADER_NAME            = 'automatic_class';
    const DEFAULT_ACTION         = 'indexAction';
    const DEFAULT_CONTROLLER     = 'DefaultController';
    const CONTROLLER_FILE_SUFFIX = 'Controller';
    const ACTION_METHOD_SUFFIX   = 'Action';
    const BUNDLE_NAME_SUFFIX     = 'Bundle';

    public function load($class, $type = null) {
        $routes = new RouteCollection();

        if (!class_exists($class)) {
            throw new InvalidArgumentException(sprintf('Class "%s" does not exist.', $class));
        }

        $class = new ReflectionClass($class);
        /* @var $method RelectionMethod  */
        foreach ($class->getMethods() as $method) {
            $this->defaultRouteIndex = 0;
            if ($this->isValidActionMethod($method)) {
                $this->addRoute($routes, $class, $method);
            }
        }

        return $routes;
    }

    /**
     * @param ReflectionMethod $method
     * @return bool
     */
    protected function isValidActionMethod(ReflectionMethod $method) {
        if ('Action' != substr($method->getName(), -6)) {
            return false;
        }

        return $method->isPublic();
    }

    public function supports($resource, $type = null) {
        return static::LOADER_NAME === $type;
    }

    /**
     * @param string $path
     * @param array $defaults
     * @param array $requirements
     * @param array $options
     * @param string $host
     * @param array $schemes
     * @param array $methods
     * @param string $condition
     * @return Route
     */
    protected function createRoute($path, array $defaults, array $requirements = [], array $options = [], $host = '', array $schemes = [], array $methods = [], $condition = '') {
        return new Route($path, $defaults, $requirements, $options, $host, $schemes, $methods, $condition);
    }

    /**
     * @param RouteCollection $collection
     * @param ReflectionClass $class
     * @param ReflectionMethod $method
     * @return $this
     */
    protected function addRoute(RouteCollection $collection, ReflectionClass $class, ReflectionMethod $method) {
        $classParts        = explode('\\', $class->getName());
        $nameParts         = [];
        $isControllerParts = false;
        $bundleNameShift   = -1 * strlen(static::BUNDLE_NAME_SUFFIX);
        foreach ($classParts as $i => $classPart) {
            if ($isControllerParts) {
                if (static::CONTROLLER_FILE_SUFFIX == substr($classPart, -1 * strlen(static::CONTROLLER_FILE_SUFFIX))) {
                    $nameParts[] = substr($classPart, 0, -10);
                }
                else {
                    $nameParts[] = $classPart;
                }
            }

            if (static::CONTROLLER_FILE_SUFFIX == $classPart && $i > 0 && static::BUNDLE_NAME_SUFFIX == substr($classParts[$i - 1], $bundleNameShift)) {
                $isControllerParts = true;
                $nameParts[]       = substr($classParts[$i - 1], 0, $bundleNameShift);
            }
        }

        $nameParts[]         = substr($method->getName(), 0, -1 * strlen(static::ACTION_METHOD_SUFFIX));
        $controllerParts     = $nameParts;
        $controllerParts[0] .= static::BUNDLE_NAME_SUFFIX;
        $defaults    = [
            '_controller' => join(':', $controllerParts),
        ];

        foreach ($method->getParameters() as $param) {
            if (!isset($defaults[$param->getName()]) && $param->isDefaultValueAvailable()) {
                $defaults[$param->getName()] = $param->getDefaultValue();
            }
        }

        $path  = lcfirst(join('/', $nameParts));
        $name  = join('_', $nameParts);
        $route = $this->createRoute($path, $defaults);
        $collection->add($name, $route);

        if (static::DEFAULT_ACTION == $method->getName()) {

            $defaultActionParts = $nameParts;
            array_pop($defaultActionParts);
            $defaultActionPath  = lcfirst(join('/', $defaultActionParts));
            $defaultActionName  = join('_', $defaultActionParts);
            $defaultActionRoute = $this->createRoute($defaultActionPath, $defaults);
            $collection->add($defaultActionName, $defaultActionRoute);

            if (static::DEFAULT_CONTROLLER == $class->getShortName()) {
                $defaultControllerPath  = '/'.lcfirst($defaultActionParts[0]);
                $defaultControllerName  = $defaultActionParts[0];
                $defaultControllerRoute = $this->createRoute($defaultControllerPath, $defaults);
                $collection->add($defaultControllerName, $defaultControllerRoute);
            }
        }

        return $this;
    }
}