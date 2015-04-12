<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Test\Generator\Schema;

use Rocket\ORM\Generator\Schema\Loader\SchemaLoader;
use Rocket\ORM\Generator\Schema\Schema;
use Rocket\ORM\Generator\Schema\Transformer\SchemaTransformerInterface;
use Rocket\ORM\Test\Generator\Schema\Loader\InlineSchemaLoader;
use Symfony\Component\Finder\Finder;
use Symfony\Component\Yaml\Yaml;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 *
 * @method bool assertEquals($expected, $value, $message = null)
 * @method bool assertCount($expected, $countable, $message = null)
 */
trait SchemaTestHelper
{
    /**
     * @param SchemaLoader $schemaLoader
     * @param string       $assertion
     * @param string|null  $assertionMessage
     */
    public function assertSchemaLoadingException(SchemaLoader $schemaLoader, $assertion, $assertionMessage = null)
    {
        $error = null;
        try {
            $schemaLoader->load();
        } catch (\Exception $e) {
            $error = $e->getMessage();
        }

        $this->assertEquals($error, $assertion, $assertionMessage);
    }

    /**
     * @param string|null                $schemasDir
     * @param SchemaTransformerInterface $schemaTransformer
     * @param string                     $schemaModelNamespace
     *
     * @return array|\Rocket\ORM\Generator\Schema\Schema[]
     */
    public function getSchemas($schemasDir = null, SchemaTransformerInterface $schemaTransformer = null,
                               $schemaModelNamespace = '\\Rocket\\ORM\\Generator\\Schema\\Schema')
    {
        if (null == $schemasDir) {
            $schemasDir = __DIR__ . '/../../../../../../resources/schemas';
        }

        $options = [];
        if (null != $schemaTransformer) {
            $options['transformer'] = ['class' => $schemaTransformer];
        }

        if (null != $schemaModelNamespace) {
            $options['model'] = [
                'schema' => [
                    ['class' => $schemaModelNamespace]
                ]
            ];
        }

        $schemaLoader = new SchemaLoader($schemasDir, [], $options);
        $schemas = $schemaLoader->load();

        $finder = new Finder();
        $finder
            ->files()
            ->in($schemasDir)
        ;

        $this->assertCount($finder->count(), $schemas, 'Schemas count');

        return $schemas;
    }

    /**
     * @param string      $schemaName
     * @param null|string $schemaDir
     *
     * @return Schema
     */
    public function getSchemaByName($schemaName, $schemaDir = null)
    {
        if (null == $schemaDir) {
            $schemaDir = __DIR__ . '/../../../../../../resources/schemas';
        }

        $schemaPath = $schemaDir . DIRECTORY_SEPARATOR . $schemaName;
        if (!is_file($schemaPath)) {
            throw new \InvalidArgumentException('The schema ' . $schemaPath . ' is not found');
        }

        $schemaLoader = new InlineSchemaLoader([
            $schemaPath => Yaml::parse(file_get_contents($schemaPath))
        ]);

        $schemas = $schemaLoader->load();
        $this->assertCount(1, $schemas);

        return $schemas[0];
    }
}
