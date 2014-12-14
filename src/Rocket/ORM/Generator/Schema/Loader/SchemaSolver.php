<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\Schema\Loader;

use Rocket\ORM\Generator\Schema\Loader\Exception\SchemaNotFoundException;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class SchemaLoader
{
    /**
     * @var string
     */
    protected $path;


    /**
     * @param $path
     */
    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @throws SchemaNotFoundException
     */
    public function load()
    {
        $schemas = $this->getSchemas();
    }

    /**
     * @return array
     *
     * @throws SchemaNotFoundException
     */
    protected function getSchemas()
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in($this->path)
            ->name('/(.*)?schema.yml/')
        ;

        if (!isset($finder[0])) {
            throw new SchemaNotFoundException('Schema not found in path "' . $this->path . '"');
        }

        $schemas = [];

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            $schemas[] = Yaml::parse($file->getContents());
        }

        return $schemas;
    }
}
