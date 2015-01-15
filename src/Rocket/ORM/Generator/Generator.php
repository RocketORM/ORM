<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

namespace Rocket\ORM\Generator;

/**
 * @author Sylvain Lorinet <sylvain.lorinet@gmail.com>
 */
abstract class Generator implements GeneratorInterface
{
    /**
     * @param string $directory
     */
    protected function createDirectory($directory)
    {
        if (!is_dir($directory)) {
            if (!@mkdir($directory, 755, true)) {
                throw new \RuntimeException(
                    'The generator "' . end(explode('\\', get_called_class())) . '" cannot create directory "'
                    . $directory . '", error message : ' . error_get_last()['message']
                );
            }
        }
    }
}
