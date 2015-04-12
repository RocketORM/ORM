<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator\Schema\Transformer;

use Rocket\ORM\Generator\Schema\Column;
use Rocket\ORM\Generator\Schema\Schema;
use Rocket\ORM\Generator\Schema\Table;
use Rocket\ORM\Generator\Utils\StringUtil;
use Rocket\ORM\Model\Map\TableMap;
use Symfony\Component\Config\Definition\Exception\InvalidConfigurationException;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
class SchemaTransformer implements SchemaTransformerInterface
{
    /**
     * @var array
     */
    protected $modelsNamespace = [];


    /**
     * @param array $classes
     * @param array $defaultClasses
     */
    public function __construct(array $classes, array $defaultClasses)
    {
        foreach ($classes as $type => $class) {
            if (!$this->validateSchemaModelClass($class['class'], $defaultClasses[$type]['class'])) {
                throw new \InvalidArgumentException(
                    'The ' . $type . ' model ("' . $class['class'] . '") '
                    . 'model must extend "' . $defaultClasses[$type]['class'] . '"'
                );
            }

            $this->modelsNamespace[$type] = $class['class'];
        }
    }

    /**
     * @param array  $schemaData The schema data
     * @param string $path       The absolute path to the schema file
     *
     * @return Schema
     */
    public function transform(array $schemaData, $path)
    {
        /** @var Schema $schema */
        $class = $this->modelsNamespace['schema'];
        $classes = $this->modelsNamespace;
        unset($classes['schema']);

        $schema = new $class($schemaData, $classes);

        // Escape anti slashes
        $schema->namespace = str_replace('\\\\', '\\', $schema->namespace);
        $schema->escapedNamespace = str_replace('\\', '\\\\', $schema->namespace);

        $this->formatDirectory($schema);

        // Delete the file in the path
        $pathParams = explode(DIRECTORY_SEPARATOR, $path);
        unset($pathParams[sizeof($pathParams) - 1]);

        // TODO check if the connection name exists

        $schema->absoluteDirectory = join(DIRECTORY_SEPARATOR, $pathParams) . $schema->relativeDirectory;

        $this->transformTables($schema->getTables());

        return $schema;
    }

    /**
     * @param array|Table[] $tables
     */
    public function transformTables(array $tables)
    {
        foreach ($tables as $table) {
            if (null == $table->phpName) {
                $table->phpName = StringUtil::camelize($table->name);
            }

            // TODO check if table phpName is named "RocketBaseModel"
            // TODO check if table has a primary key, RocketORM do not support a table without PK

            $this->transformColumns($table->getColumns());
            $this->transformPrimaryKeys($table->getColumns());
        }
    }

    /**
     * @param array|Column[] $columns
     */
    public function transformColumns(array $columns)
    {
        foreach ($columns as $column) {
            if (null == $column->phpName) {
                $column->phpName = StringUtil::camelize($column->name, false);
            }

            if (true === $column->isPrimaryKey) {
                $column->isRequired = true;
            }

            // TODO check for more than one autoIncrement PK
            // TODO column TEXT can't have a default value
            // TODO column DATETIME can't have a default value

            // Check if default value is valid if the type is boolean
            if (TableMap::COLUMN_TYPE_BOOLEAN == $column->type && null !== $column->getDefault()
                && true !== $column->getDefault() && false !== $column->getDefault()) {
                throw new InvalidConfigurationException(
                    'The default value "' . $column->getDefault(true) . '" for boolean column "' . $column->name . '" on table "' . $column->getTable()->name . '" should be a boolean'
                );
            }

            // Check, for enum type, if the default value exists in the values array
            if (TableMap::COLUMN_TYPE_ENUM === $column->type && null != $column->getDefault(true)
                && !in_array($column->getDefault(true), $column->values)) {
                throw new InvalidConfigurationException(
                    'Invalid default value "' . $column->getDefault(true) . '" for enum column "' . $column->name . '" on table "' . $column->getTable()->name . '"'
                );
            }

            if ((TableMap::COLUMN_TYPE_DOUBLE == $column->type || TableMap::COLUMN_TYPE_FLOAT == $column->type)) {
                if (null === $column->size || null === $column->decimal) {
                    $column->size = null;
                    $column->decimal = null;
                } elseif ($column->decimal >= $column->size) {
                    throw new InvalidConfigurationException(
                        'Invalid size value "' . $column->size . '" for column "' . $column->name . '" on table "'
                        . $column->getTable()->name . '", the size should be greater than the decimal value "' . $column->decimal . '"'
                    );
                }
            }
        }
    }

    /**
     * @param array|Column[] $columns
     */
    public function transformPrimaryKeys(array $columns)
    {
        foreach ($columns as $column) {
            if (true === $column->isPrimaryKey) {
                $column->getTable()->addPrimaryKey($column);
            }
        }
    }

    /**
     * @param Schema $schema
     */
    protected function formatDirectory(Schema $schema)
    {
        // Add or delete slashes
        // Add first slash if missing
        if (0 < strpos($schema->relativeDirectory, DIRECTORY_SEPARATOR)) {
            $schema->relativeDirectory = DIRECTORY_SEPARATOR . $schema->relativeDirectory;
        }

        // Delete last slash if exists
        if (DIRECTORY_SEPARATOR === $schema->relativeDirectory[strlen($schema->relativeDirectory) - 1]) {
            $schema->relativeDirectory = substr($schema->relativeDirectory, 0, -1);
        }
    }

    /**
     * @param string $class
     * @param string $defaultClass
     *
     * @return bool
     */
    protected function validateSchemaModelClass($class, $defaultClass)
    {
        if ($class != $defaultClass) {
            $class = new \ReflectionClass($class);

            if (!$class->isSubclassOf($defaultClass)) {
                return false;
            }
        }

        return true;
    }
}
