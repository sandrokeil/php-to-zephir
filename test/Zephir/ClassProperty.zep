namespace PhpToZephirTest\Mock;

class ClassProperty
{
    protected myProperty;
    public function setMyProperty(var myProperty) -> void
    {
        let this->myProperty = myProperty;
    }

    public function getMyProperty()
    {
        return this->myProperty;
    }

}