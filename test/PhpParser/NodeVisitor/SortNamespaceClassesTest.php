<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/php-to-zephir for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/php-to-zephir/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace PhpToZephirTest\PhpParser\NodeVisitor;

use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\PrettyPrinter\Standard;
use PhpToZephir\PhpParser\NodeVisitor\SortNamespaceClasses;
use PHPUnit\Framework\TestCase;

class SortNamespaceClassesTest extends TestCase
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
     * @var Standard
     */
    private $printer;

    /**
     * @var SortNamespaceClasses
     */
    private $sortNamespaceClasses;

    protected function setUp()
    {
        $this->sortNamespaceClasses = new SortNamespaceClasses();
        $this->parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new NameResolver());
        $this->traverser->addVisitor($this->sortNamespaceClasses);
        $this->printer = new Standard();
    }

    /**
     * @test
     */
    public function it_sorts_interfaces(): void
    {
        $code =  <<<'CODE'
<?php
namespace Psr\Http\Client;

interface ClientInterface
{
    public function sendRequest(\Psr\Http\Message\RequestInterface $request) : \Psr\Http\Message\ResponseInterface;
}
namespace Psr\Http\Client;

interface NetworkExceptionInterface extends \Psr\Http\Client\ClientExceptionInterface
{
    public function getRequest() : \Psr\Http\Message\RequestInterface;
}
namespace Psr\Http\Client;

interface RequestExceptionInterface extends \Psr\Http\Client\ClientExceptionInterface
{
    public function getRequest() : \Psr\Http\Message\RequestInterface;
}
namespace Psr\Http\Client;

interface ClientExceptionInterface extends \Throwable
{
}
CODE;
        $exptectedCode =  <<<'CODE'
namespace Psr\Http\Client;

interface ClientInterface
{
    public function sendRequest(\Psr\Http\Message\RequestInterface $request) : \Psr\Http\Message\ResponseInterface;
}
interface ClientExceptionInterface extends \Throwable
{
}
interface NetworkExceptionInterface extends \Psr\Http\Client\ClientExceptionInterface
{
    public function getRequest() : \Psr\Http\Message\RequestInterface;
}
interface RequestExceptionInterface extends \Psr\Http\Client\ClientExceptionInterface
{
    public function getRequest() : \Psr\Http\Message\RequestInterface;
}

CODE;

        $ast = $this->parser->parse($code);
        $this->traverser->traverse($ast);

        $current = $this->sortNamespaceClasses->printSortedNamespaces($this->printer);

        $this->assertEquals(
            $exptectedCode,
            $current,
            $current
        );
    }
}