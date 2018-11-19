/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/php-to-zephir for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/php-to-zephir/blob/master/LICENSE.md New BSD License
 */

namespace PhpToZephirTest\Mock;

class ClassReturnArray
{
    public function assoc() -> array
    {
        return ["test": true, "first": "ok", 3: 123];
    }

    public function index() -> array
    {
        return ["first", 2, true];
    }

}