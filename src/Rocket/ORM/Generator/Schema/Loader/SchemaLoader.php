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

use Rocket\ORM\Generator\Schema\Configuration\SchemaConfiguration;
use Rocket\ORM\Generator\Schema\Loader\Exception\InvalidConfigurationException;
use Rocket\ORM\Generator\Schema\Loader\Exception\SchemaNotFoundException;
use Rocket\ORM\Generator\Schema\SchemaInterface;
use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformerInterface;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException as ConfigurationException;
use Symfony\Component\Config\Definition\Processor;
use Symfony\Component\Config\Util\XmlUtils;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Finder\SplFileInfo;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class SchemaLoader
{
    /**
     * @var string|array
     */
    protected $path;

    /**
     * @var string|array
     */
    protected $exclude;

    /**
     * @var string
     */
    protected $modelNamespace;

    /**
     * @var SchemaTransformerInterface
     */
    protected $schemaTransformer;


    /**
     * @param string|array                    $path
     * @param string|array                    $exclude
     * @param string                          $modelNamespace
     * @param SchemaTransformerInterface|null $schemaTransformer
     */
    public function __construct($path, $exclude = [], $modelNamespace = '\\Rocket\\ORM\\Generator\\Schema\\Schema', SchemaTransformerInterface $schemaTransformer = null)
    {
        $this->path              = $path;
        $this->exclude           = $exclude;
        $this->schemaTransformer = $schemaTransformer;

        $class = new \ReflectionClass($modelNamespace);
        if (!$class->implementsInterface('\\Rocket\\ORM\\Generator\\Schema\\SchemaInterface')) {
            throw new \InvalidArgumentException('The schema model must implement Rocket\ORM\Generator\Schema\SchemaInterface');
        }

        $this->modelNamespace = $modelNamespace;
    }

    /**
     * @return SchemaInterface[]
     *
     * @throws InvalidConfigurationException
     * @throws SchemaNotFoundException
     */
    public function load()
    {
        $schemaLocations = $this->getSchemasLocation();
        $schemas = [];

        foreach ($schemaLocations['xml'] as $path) {
            $schemas[$path] = $this->parseXml($path);
        }

        foreach ($schemaLocations['yml'] as $path) {
            $schemas[$path] = $this->parseYaml($path);
        }

        return $this->validate($schemas);
    }

    /**
     * @param array $schemas
     *
     * @return array
     *
     * @throws InvalidConfigurationException
     */
    protected function validate(array $schemas)
    {
        $processor = new Processor();
        $configuration = new SchemaConfiguration();

        $validSchemas = [];

        foreach ($schemas as $path => $schema) {
            try {
                $validSchemas[$path] = new $this->modelNamespace($processor->processConfiguration($configuration, [$schema]), $path, $this->schemaTransformer);
            } catch (ConfigurationException $e) {
                throw new InvalidConfigurationException($path, $e);
            }
        }

        return $validSchemas;
    }

    /**
     * @return array
     *
     * @throws SchemaNotFoundException
     */
    protected function getSchemasLocation()
    {
        $finder = new Finder();
        $finder
            ->files()
            ->in($this->path)
            ->exclude($this->exclude)
            ->name('/(.*)?schema.(yml|xml)/')
        ;

        if (0 === $finder->count()) {
            throw new SchemaNotFoundException('Schema not found in path "' . $this->path . '"');
        }

        $schemas = [
            'xml' => [],
            'yml' => []
        ];

        /** @var SplFileInfo $file */
        foreach ($finder as $file) {
            if ('xml' == strtolower($file->getExtension())) {
                $schemas['xml'][] = $file->getRealPath();
            } elseif ('yml' == strtolower($file->getExtension())) {
                $schemas['yml'][] = $file->getRealPath();
            }

            // else, extension not implemented
        }

        return $schemas;
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function parseXml($path)
    {
        return XmlUtils::convertDomElementToArray(XmlUtils::loadFile($path)->documentElement);
    }

    /**
     * @param string $path
     *
     * @return array
     */
    protected function parseYaml($path)
    {
        return Yaml::parse($path);
    }
}
