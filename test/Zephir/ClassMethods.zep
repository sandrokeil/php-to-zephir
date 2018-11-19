namespace PhpToZephirTest\Mock;

class ClassMethods
{
    public function doSum1(int a, int b) -> int
    {
        return a + b;
    }

    public function doSum2(int a, int b = 3) -> int
    {
        return a + b;
    }

    public function doSum3(int a = 1, int b = 2) -> int
    {
        return a + b;
    }

    public function doSum4(int a, int b) -> int
    {
        return a + b;
    }

}