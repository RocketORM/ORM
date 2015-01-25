<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$loader = require __DIR__ . '/../vendor/autoload.php';

/** @var \Composer\Autoload\ClassLoader $loader */
$loader->add('Rocket', __DIR__ . '/framework');
$loader->add('Fixture', __DIR__ . '/resources/fixtures');

// Initialize tests
$kernel = new \Rocket\ORM\Test\Kernel\TestKernel(
    __DIR__ . '/resources/fixtures/databases',
    __DIR__ . '/resources/fixtures/sql',
    __DIR__ . '/resources/config/rocket.yml',
    __DIR__ . '/resources/schemas'
);
$kernel->init();