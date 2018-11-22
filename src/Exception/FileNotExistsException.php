<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/php-to-zephir for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/php-to-zephir/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhpToZephir\Exception;

class FileNotExistsException extends RuntimeException
{
    public static function forFile(string $file): FileNotExistsException
    {
        return new self(
            sprintf('"%s" does not exists.', $file)
        );
    }
}
