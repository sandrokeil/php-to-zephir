<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/php-to-zephir for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/php-to-zephir/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhpToZephir\Console\Command;

use PhpToZephir\Exception\CouldNotCreateDirectoryException;
use PhpToZephir\Exception\CouldNotWriteFileException;
use PhpToZephir\Exception\RuntimeException;
use Symfony\Component\Console\Input\InputArgument;
use PhpParser\Node;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;

class Prototype extends AbstractCommand
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
     * @var string
     */
    private $prototypeCode;

    public function __construct()
    {
        parent::__construct();
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);

        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new class extends NodeVisitorAbstract {
            public function leaveNode(Node $node)
            {
                if ($node instanceof Node\Stmt\Declare_) {
                    return NodeTraverser::REMOVE_NODE;
                }
                if ($node instanceof Node\Stmt\Use_) {
                    return NodeTraverser::REMOVE_NODE;
                }
            }
            public function enterNode(Node $node)
            {
                if ($node instanceof Node\Stmt\ClassMethod) {
                    // Clean out the function body
                    $node->stmts = [];
                }
            }
        });

        $this->printer = new Standard();
    }

    protected function configure()
    {
        $this
            ->setName('php2zephir:prototype:create')
            ->setDescription('Creates prototypes of given file or directory')
            ->addArgument('from', InputArgument::REQUIRED, 'Source path or file')
            ->addArgument('to', InputArgument::REQUIRED, 'Destination path of Zephir prototype file');
    }

    protected function processFileContent(string $file, string $fileContent): void
    {
        $ast = $this->parser->parse($fileContent);
        $ast = $this->traverser->traverse($ast);
        $this->prototypeCode  .= $this->printer->prettyPrint($ast);
    }

    protected function finished(): void
    {
        $to = realpath($this->to);
        if ($to && is_dir($to)) {
            throw new RuntimeException(sprintf('To must be a file and not a directory, "%s" given', $this->to));
        }

        $dir = dirname($this->to);

        if (! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw CouldNotCreateDirectoryException::forDir($dir);
        }

        if (false === file_put_contents($this->to, "<?php \n" . $this->prototypeCode)) {
            throw CouldNotWriteFileException::forFile($to);
        }

        $this->prototypeCode = '';
    }
}
