<?php

/*
 * This file is part of the "RocketORM" package.
 *
 * https://github.com/RocketORM/ORM
 *
 * For the full license information, please view the LICENSE
 * file that was distributed with this source code.
 */

$loader    = null;
$autoloads = [
    __DIR__ . '/../vendor/autoload.php',
    __DIR__ . '/../../../autoload.php'
];

foreach ($autoloads as $file) {
    if (file_exists($file)) {
        $loader = require $file;

        break;
    }
}

if (null == $loader) {
    throw new \RuntimeException('The composer autoload (vendor/autoload.php) is not found');
}

use Rocket\ORM\Console\RocketApplication;
use Symfony\Component\Finder\Finder;

$finder = new Finder();
$finder
    ->files()
    ->in(__DIR__ . '/../src/Rocket/ORM/Command')
    ->name('Rocket*Command.php')
    ->depth(0)
;

$app = new RocketApplication();
$app->addCommands($app->resolveCommands($finder));

$app->run();
