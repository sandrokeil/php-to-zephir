<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/php-to-zephir for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/php-to-zephir/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhpToZephirTest\Mock;

class ClassMethods
{
    public function doSum1(int $a, int $b): int
    {
        return $a + $b;
    }

    public function doSum2(int $a, int $b = 3): int
    {
        return $a + $b;
    }

    public function doSum3(int $a = 1, int $b = 2): int
    {
        return $a + $b;
    }

    public function doSum4(int $a, int $b): int
    {
        return $a + $b;
    }
}