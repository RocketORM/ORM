<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Config;

use Rocket\ORM\Config\Exception\ConfigurationFileNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class ConfigLoader
{
    /**
     * @var array
     */
    protected $config;


    /**
     * @param string|null $path
     * @param array       $options
     */
    public function __construct($path = null, array $options = [])
    {
        $this->load($path, $options);
    }

    /**
     * @param string $path
     * @param array  $options
     *
     * @throws ConfigurationFileNotFoundException
     */
    protected function load($path, array $options)
    {
        if (null != $path) {
            if (!is_file($path)) {
                throw new ConfigurationFileNotFoundException('The rocket configuration file is not found in the selected folder "' . $path . '"');
            }
        } else {
            $rootDir = getcwd();
            $finder = new Finder();
            $dirs = [
                $rootDir . '/config',
                $rootDir . '/configs'
            ];

            $availableDirs = [$rootDir];
            foreach ($dirs as $dir) {
                if (is_dir($dir)) {
                    $availableDirs[] = $dir;
                }
            }

            $finder
                ->files()
                ->in($availableDirs)
                ->name('rocket.yml')
                ->depth(0)
            ;

            /** @var SplFileInfo $file */
            $files = $finder->getIterator();
            $files->rewind();
            $file = $files->current();

            if (null == $file) {
                throw new ConfigurationFileNotFoundException('The rocket configuration file is not found. Please create a new one into your root folder or in a folder named "/config" or "/configs".');
            }

            $path = $file->getRealPath();

        }

        $this->config = array_merge(Yaml::parse($path), $options)['rocket'];
    }

    /**
     * @return array
     */
    public function all()
    {
        return $this->config;
    }
}
