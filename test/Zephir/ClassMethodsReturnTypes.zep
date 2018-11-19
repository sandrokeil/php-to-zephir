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

}