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
use Rocket\ORM\Generator\Schema\Schema;
use Rocket\ORM\Generator\Schema\Transformer\SchemaRelationTransformerInterface;
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
     * @var array
     */
    protected $options;


    /**
     * @param string|array $path
     * @param array        $options
     */
    public function __construct($path, array $options = [])
    {
        $this->path    = $path;
        $this->options = array_replace_recursive($this->getDefaultOptions(), $options);
    }

    /**
     * @return array|Schema[]
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

        $defaultOptions = $this->getDefaultOptions();
        $transformerClass = $this->options['transformer']['schema']['class'];

        if (is_object($transformerClass)) {
            $schemaTransformer = $transformerClass;
        } else {
            $schemaTransformer = new $transformerClass($this->options['model'], $defaultOptions['model']);
        }

        if (!$schemaTransformer instanceof SchemaTransformerInterface) {
            throw new \InvalidArgumentException(
                'The schema transformer class "' . $transformerClass . '" should implements '
                . '\Rocket\ORM\Generator\Schema\Transformer\SchemaTransformerInterface'
            );
        }

        $normalizedSchemas = [];
        foreach ($schemas as $path => $schema) {
            try {
                $normalizedSchemas[$path] = $schemaTransformer->transform(
                    $processor->processConfiguration($configuration, [$schema]),
                    $path
                );
            } catch (ConfigurationException $e) {
                throw new InvalidConfigurationException($path, $e);
            }
        }

        return $this->validateRelations($normalizedSchemas);
    }

    /**
     * @param array|Schema[] $normalizedSchemas
     *
     * @return array|Schema[]
     *
     * @throws InvalidConfigurationException
     */
    protected function validateRelations(array $normalizedSchemas)
    {
        $transformerClass = $this->options['transformer']['relation']['class'];
        if (is_object($transformerClass)) {
            $schemaRelationTransformer = $transformerClass;
        } else {
            $schemaRelationTransformer = new $transformerClass();
        }

        if (!$schemaRelationTransformer instanceof SchemaRelationTransformerInterface) {
            throw new \InvalidArgumentException(
                'The schema relation transformer class "' . $transformerClass . '" should implements '
                . '\Rocket\ORM\Generator\Schema\Transformer\SchemaRelationTransformerInterface'
            );
        }

        $validSchemas = [];

        // All schemas are loaded, now we can validate relations
        /** @var Schema $schema */
        foreach ($normalizedSchemas as $path => $schema) {
            try {
                foreach ($schema->getTables() as $table) {
                    $schemaRelationTransformer->transform($table, $normalizedSchemas);
                }

                // Must wait for the relation transformations above ($relation->with)
                foreach ($schema->getTables() as $table) {
                    $schemaRelationTransformer->transformRelatedRelations($table, $normalizedSchemas);
                }
            } catch (ConfigurationException $e) {
                throw new InvalidConfigurationException($path, $e);
            }

            $validSchemas[] = $schema;
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
            ->exclude($this->options['exclude'])
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

    /**
     * Return the default available configurable options
     *
     * @return array
     */
    protected function getDefaultOptions()
    {
        return [
            'exclude'     => null,
            'model'       => [
                'schema'   => ['class' => '\Rocket\ORM\Generator\Schema\Schema'],
                'table'    => ['class' => '\Rocket\ORM\Generator\Schema\Table'],
                'column'   => ['class' => '\Rocket\ORM\Generator\Schema\Column'],
                'relation' => ['class' => '\Rocket\ORM\Generator\Schema\Relation']
            ],
            'transformer' => [
                'schema'   => ['class' => '\Rocket\ORM\Generator\Schema\Transformer\SchemaTransformer'],
                'relation' => ['class' => '\Rocket\ORM\Generator\Schema\Transformer\SchemaRelationTransformer']
            ]
        ];
    }
}
