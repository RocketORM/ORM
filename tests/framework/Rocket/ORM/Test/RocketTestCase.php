<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Test;

use \Rocket\ORM\Config\ConfigLoader;
use Rocket\ORM\Rocket;
use Rocket\ORM\Test\Helper\TestHelper;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class RocketTestCase extends \PHPUnit_Framework_TestCase
{
    /**
     * @var string
     */
    protected $rootDir;

    /**
     * @var array|TestHelper[]
     */
    private $helpers;


    /**
     *
     */
    public function __construct()
    {
        $this->rootDir = __DIR__ . '/../../../..';
    }

    /**
     *
     */
    public function setUp()
    {
        $this->initHelpers();

        $configLoader = new ConfigLoader(__DIR__ . '/../../../../../rocket.yml');
        Rocket::setConfiguration($configLoader->all());
    }

    /**
     * Init test helpers
     */
    protected function initHelpers()
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in(__DIR__)
            ->name('*TestHelper.php')
            ->notName('TestHelper.php')
        ;

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $classNamespace = '\\Rocket\\ORM\\Test\\' . str_replace(DIRECTORY_SEPARATOR, '\\', substr($file->getRealPath(), strlen(__DIR__ . '/'), -strlen('.php')));
            $helper = new $classNamespace();

            $this->helpers[$helper->getHelperName()] = $helper;
        }
    }

    /**
     * @param string $name
     *
     * @return TestHelper
     */
    protected function getHelper($name)
    {
        if (!isset($this->helpers[$name])) {
            throw new \InvalidArgumentException('Unknown test helper named "' . $name . '"');
        }

        return $this->helpers[$name];
    }
}
