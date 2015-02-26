<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

echo '
    ______           _        _
    | ___ \         | |      | |
    | |_/ /___   ___| | _____| |_
    |    // _ \ / __| |/ / _ \ __|
    | |\ \ (_) | (__|   <  __/ |_
    \_| \_\___/ \___|_|\_\___|\__|

';

$loader = require __DIR__ . '/../vendor/autoload.php';

/** @var \Composer\Autoload\ClassLoader $loader */
$loader->add('Rocket', __DIR__ . '/framework');
$loader->add('Fixture', __DIR__ . '/resources/fixtures');

define('TEST_CACHE_DIR', __DIR__ . '/resources/fixtures/cache');

// Initialize tests
$kernel = new \Rocket\ORM\Test\Kernel\TestKernel(
    TEST_CACHE_DIR,
    __DIR__ . '/resources/fixtures/sql',
    __DIR__ . '/resources/config/rocket.yml',
    __DIR__ . '/resources/schemas'
);
$kernel->init();