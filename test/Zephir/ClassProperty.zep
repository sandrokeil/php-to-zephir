/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/php-to-zephir for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/php-to-zephir/blob/master/LICENSE.md New BSD License
 */

namespace PhpToZephirTest\Mock;

class ClassProperty
{
    protected myProperty;
    /**
     * @var MyClass
     */
    protected myClass;
    public function setMyProperty(var myProperty) -> void
    {
        let this->myProperty = myProperty;
    }

    public function setMyClass(<MyClass> myClass) -> void
    {
        let this->myClass = myClass;
    }

    public function getMyProperty()
    {
        return this->myProperty;
    }

}