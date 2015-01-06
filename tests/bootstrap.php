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