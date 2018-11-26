<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/php-to-zephir for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/php-to-zephir/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace PhpToZephirTest;

use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpToZephir\PhpParser\NodeVisitor\InitLocalVariable;
use PhpToZephir\ZephirPrinter;
use PHPUnit\Framework\TestCase;

class ZephirPrinterTest extends TestCase
{
    /**
     * @var \PhpParser\Parser
     */
    private $parser;

    /**
     * @var NodeTraverser
     */
    private $traverser;

    /**
     * @var ZephirPrinter
     */
    private $zephirPrinter;

    protected function setUp()
    {
        $this->parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new InitLocalVariable());
        $this->zephirPrinter = new ZephirPrinter();
    }

    /**
     * @test
     */
    public function it_converts_return_type_string(): void
    {
        $code = <<<'CODE'
<?php

function test($foo): string
{
}
CODE;

        $expectedCode = <<<'CODE'
function test(foo) -> string
{
}
CODE;

        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);

        $current = $this->zephirPrinter->prettyPrintFile($ast);

        $this->assertEquals($expectedCode, $current, $current);
    }

    /**
     * @test
     */
    public function it_converts_function_types(): void
    {
        $code = <<<'CODE'
<?php

function test(string $foo): string
{
}
CODE;

        $expectedCode = <<<'CODE'
function test(string foo) -> string
{
}
CODE;

        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);

        $current = $this->zephirPrinter->prettyPrintFile($ast);

        $this->assertEquals($expectedCode, $current, $current);
    }

    /**
     * @test
     */
    public function it_converts_return_type_class(): void
    {
        $code = <<<'CODE'
<?php

use PhpToZephirTest\Mock\MyClass;

function test($foo): MyClass
{
}
CODE;

        $expectedCode = <<<'CODE'
use PhpToZephirTest\Mock\MyClass;
function test(foo) -> <MyClass>
{
}
CODE;

        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);

        $this->assertEquals($expectedCode, $this->zephirPrinter->prettyPrintFile($ast));
    }

    /**
     * @test
     */
    public function it_converts_class_methods(): void
    {
        $code = file_get_contents(__DIR__ . '/Mock/ClassMethods.php');
        $expectedCode = file_get_contents(__DIR__ . '/Zephir/ClassMethods.zep');

        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);

        $current = $this->zephirPrinter->prettyPrintFile($ast);

        $this->assertEquals($expectedCode, $current, $current);
    }

    /**
     * @test
     */
    public function it_converts_class_property(): void
    {
        $code = file_get_contents(__DIR__ . '/Mock/ClassProperty.php');
        $expectedCode = file_get_contents(__DIR__ . '/Zephir/ClassProperty.zep');

        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);
        $current = $this->zephirPrinter->prettyPrintFile($ast);

        $this->assertEquals($expectedCode, $current, $current);
    }

    /**
     * @test
     */
    public function it_converts_class_methods_return_types(): void
    {
        $code = file_get_contents(__DIR__ . '/Mock/ClassMethodsReturnTypes.php');
        $expectedCode = file_get_contents(__DIR__ . '/Zephir/ClassMethodsReturnTypes.zep');

        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);
        $current = $this->zephirPrinter->prettyPrintFile($ast);

        $this->assertEquals($expectedCode, $current, $current);
    }

    /**
     * @test
     */
    public function it_converts_class_return_array(): void
    {
        $code = file_get_contents(__DIR__ . '/Mock/ClassReturnArray.php');
        $expectedCode = file_get_contents(__DIR__ . '/Zephir/ClassReturnArray.zep');

        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);
        $current = $this->zephirPrinter->prettyPrintFile($ast);

        $this->assertEquals($expectedCode, $current, $current);
    }

    /**
     * @test
     */
    public function it_converts_class_constants(): void
    {
        $code = file_get_contents(__DIR__ . '/Mock/ClassConstants.php');
        $expectedCode = file_get_contents(__DIR__ . '/Zephir/ClassConstants.zep');

        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);
        $current = $this->zephirPrinter->prettyPrintFile($ast);

        $this->assertEquals($expectedCode, $current, $current);
    }

    /**
     * @test
     */
    public function it_converts_foreach(): void
    {
        $code = <<<'CODE'
<?php
$types = ['one', 'two', 'three'];

foreach ($types as $key => $type) {
}
CODE;

        $expectedCode = <<<'CODE'
let types = ["one", "two", "three"];
var key;
var type;
for key, type in types {
}
CODE;

        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);
        $current = $this->zephirPrinter->prettyPrintFile($ast);

        $this->assertEquals($expectedCode, $current, $current);
    }

    /**
     * @test
     */
    public function it_converts_string_concat(): void
    {
        $code = <<<'CODE'
<?php
$types = '';

$types .= 'test';
$types = $types . '/';
CODE;

        $expectedCode = <<<'CODE'
let types = "";
let types .= "test";
let types = types . "/";
CODE;

        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);
        $current = $this->zephirPrinter->prettyPrintFile($ast);

        $this->assertEquals($expectedCode, $current, $current);
    }

    /**
     * @test
     */
    public function it_converts_assign_plus(): void
    {
        $code = <<<'CODE'
<?php
$types = 1;

$types += 1;
CODE;

        $expectedCode = <<<'CODE'
let types = 1;
let types += 1;
CODE;

        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);
        $current = $this->zephirPrinter->prettyPrintFile($ast);

        $this->assertEquals($expectedCode, $current, $current);
    }

    /**
     * @test
     */
    public function it_converts_post_increment(): void
    {
        $code = <<<'CODE'
<?php
$types++;
CODE;

        $expectedCode = <<<'CODE'
let types++;
CODE;

        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);
        $current = $this->zephirPrinter->prettyPrintFile($ast);

        $this->assertEquals($expectedCode, $current, $current);
    }

    /**
     * @test
     */
    public function it_converts_coalesce(): void
    {
        $code = <<<'CODE'
<?php
$test = $types['test'] ?? 'default';
CODE;

        $expectedCode = <<<'CODE'
let test = isset(types["test"]) ? types["test"] : "default";
CODE;

        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);
        $current = $this->zephirPrinter->prettyPrintFile($ast);

        $this->assertEquals($expectedCode, $current, $current);
    }

    /**
     * @test
     */
    public function it_initializes_local_variables(): void
    {
        $code = <<<'CODE'
<?php
class TestClass
{
    public function testingFunc()
    {
        $variable = 0;
        $otherVariable = 123;
        return $otherVariable;
    }
    public function otherFunc()
    {
        $variable = 123;
        $variable = 50;
        $variable = $this->testingFunc();
        $newVariable = $this->testingFunc();
    }
}
CODE;

        $expectedCode = <<<'CODE'
class TestClass
{
    public function testingFunc()
    {
        var otherVariable;
        var variable;
        let variable = 0;
        let otherVariable = 123;
        return otherVariable;
    }

    public function otherFunc()
    {
        var newVariable;
        var variable;
        let variable = 123;
        let variable = 50;
        let variable = this->testingFunc();
        let newVariable = this->testingFunc();
    }

}
CODE;

        $ast = $this->parser->parse($code);
        $ast = $this->traverser->traverse($ast);
        $current = $this->zephirPrinter->prettyPrintFile($ast);

        $this->assertEquals($expectedCode, $current, $current);
    }
}