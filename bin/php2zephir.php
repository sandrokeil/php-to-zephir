<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/php-to-zephir for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/php-to-zephir/blob/master/LICENSE.md New BSD License
 */

namespace PhpToZephir;

if (version_compare('7.1', PHP_VERSION, '>')) {
    fwrite(
        STDERR,
        'This version of php2zephir requires PHP >= 7.1; using the latest version of PHP is highly recommended.' . PHP_EOL
    );

    die(1);
}

if (!ini_get('date.timezone')) {
    ini_set('date.timezone', 'UTC');
}

foreach (
    [
        __DIR__ . '/../../../autoload.php',
        __DIR__ . '/../../autoload.php',
        __DIR__ . '/../vendor/autoload.php',
        __DIR__ . '/vendor/autoload.php',
    ] as $file
) {
    if (file_exists($file)) {
        define('PHP2ZEHPIR_COMPOSER_INSTALL', $file);
        break;
    }
}

unset($file);

if (!defined('PHP2ZEHPIR_COMPOSER_INSTALL')) {
    fwrite(STDERR,
        'You need to set up the project dependencies using the following commands:' . PHP_EOL .
        'wget http://getcomposer.org/composer.phar' . PHP_EOL .
        'php composer.phar install' . PHP_EOL
    );

    die(1);
}

require PHP2ZEHPIR_COMPOSER_INSTALL;

use Symfony\Component\Console\Application;
use PhpToZephir\Console\Command;

$description = <<<DESC
=================================
php2zephir command line interface
=================================

Converts PHP 7 files to zep files and create prototype classes for Zephir.

DESC;

$application = new Application($description);

$application->addCommands(
    [
        new Command\Zep(),
        new Command\Prototype(),
    ]
);

$application->run();