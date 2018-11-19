/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/php-to-zephir for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/php-to-zephir/blob/master/LICENSE.md New BSD License
 */

namespace PhpToZephirTest\Mock;

class ClassMethodsReturnTypes
{
    /**
     * @param $a
     * @return bool|string
     */
    public function getSomeData(var a) -> bool|string
    {
        if (a === false) {
            return false;
        }
        return "error";
    }

    /**
     * @param $a
     * @return mixed
     */
    public function mixed(var a)
    {
    }

}