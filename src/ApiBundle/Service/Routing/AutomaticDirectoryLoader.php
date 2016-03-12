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
use Symfony\Component\Config\FileLocatorInterface;
use ApiBundle\Service\Routing\AutomaticClassLoader;

class AutomaticDirectoryLoader extends SymfonyLoader {
    const LOADER_NAME = 'automatic';

    private $isLoaded = false;

    /**
     * @var AutomaticClassLoader
     */
    private $classLoader;

    /**
     * @var FileLocatorInterface
     */
    private $locator;

    public function __construct(AutomaticClassLoader $classLoader, FileLocatorInterface $locator) {
        $this->classLoader = $classLoader;
        $this->locator     = $locator;
    }

    public function load($file, $type = null) {
        if (true === $this->isLoaded) {
            throw new \RuntimeException('Do not add the "extra" loader twice');
        }

        $dir    = $this->locator->locate($file);
        $routes = new RouteCollection();

        $files = iterator_to_array(new \RecursiveIteratorIterator(new \RecursiveDirectoryIterator($dir), \RecursiveIteratorIterator::LEAVES_ONLY));
        usort($files, function (\SplFileInfo $a, \SplFileInfo $b) {
            return (string) $a > (string) $b ? 1 : -1;
        });

        foreach ($files as $file) {
            if (!$file->isFile() || 'Controller.php' !== substr($file->getFilename(), -14)) {
                continue;
            }

            if ($class = $this->findClass($file)) {
                $refl = new \ReflectionClass($class);
                if ($refl->isAbstract()) {
                    continue;
                }

                $routes->addCollection($this->classLoader->load($class, $type));
            }
        }


        $this->isLoaded = true;

        return $routes;
    }

    public function supports($resource, $type = null) {
        return static::LOADER_NAME === $type;
    }

    /**
     * Returns the full class name for the first class in the file.
     *
     * @param string $file A PHP file path
     *
     * @return string|false Full class name if found, false otherwise
     */
    protected function findClass($file)
    {
        $class = false;
        $namespace = false;
        $tokens = token_get_all(file_get_contents($file));
        for ($i = 0; isset($tokens[$i]); ++$i) {
            $token = $tokens[$i];

            if (!isset($token[1])) {
                continue;
            }

            if (true === $class && T_STRING === $token[0]) {
                return $namespace.'\\'.$token[1];
            }

            if (true === $namespace && T_STRING === $token[0]) {
                $namespace = $token[1];
                while (isset($tokens[++$i][1]) && in_array($tokens[$i][0], array(T_NS_SEPARATOR, T_STRING))) {
                    $namespace .= $tokens[$i][1];
                }
                $token = $tokens[$i];
            }

            if (T_CLASS === $token[0]) {
                $class = true;
            }

            if (T_NAMESPACE === $token[0]) {
                $namespace = true;
            }
        }

        return false;
    }
}