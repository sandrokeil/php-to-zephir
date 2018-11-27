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

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use PhpToZephir\Exception\CouldNotCreateDirectoryException;
use PhpToZephir\Exception\CouldNotWriteFileException;
use PhpToZephir\PhpParser\NodeVisitor\InitLocalVariable;
use PhpToZephir\PhpParser\NodeVisitor\RemoveUseFunction;
use PhpToZephir\PhpParser\NodeVisitor\UnsetSplitter;
use PhpToZephir\ZephirPrinter;
use Symfony\Component\Console\Input\InputArgument;

class Zep extends AbstractCommand
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

    public function __construct()
    {
        parent::__construct();
        $this->parser = (new ParserFactory())->create(ParserFactory::ONLY_PHP7);
        $this->traverser = new NodeTraverser();
        $this->traverser->addVisitor(new InitLocalVariable());
        $this->traverser->addVisitor(new RemoveUseFunction());
        $this->traverser->addVisitor(new UnsetSplitter());
        $this->printer = new ZephirPrinter();
    }

    protected function configure()
    {
        $this
            ->setName('php2zephir:zep:create')
            ->setDescription('Creates zep file(s) of given file or directory')
            ->addArgument('from', InputArgument::REQUIRED, 'Source path or file to convert to zep file')
            ->addArgument('to', InputArgument::REQUIRED, 'Destination path or file for converted zep file');
    }

    protected function processFileContent(string $file, string $fileContent): void
    {
        $to = $this->to;

        if ($this->isDir === true) {
            $base = dirname(substr($file, strlen($this->from)));

            if ($base !== '/') {
                $base .= '/';
            }
            $to = $this->to . $base . basename($file);
        }
        $dir = dirname($to);
        $to = preg_replace('/.php$/i', '.zep', $to);

        if (! @mkdir($dir, 0755, true) && ! is_dir($dir)) {
            throw CouldNotCreateDirectoryException::forDir($dir);
        }

        $ast = $this->parser->parse($fileContent);
        $ast = $this->traverser->traverse($ast);

        if (false === file_put_contents($to, $this->printer->prettyPrintFile($ast))) {
            throw CouldNotWriteFileException::forFile($to);
        }
    }

    protected function finished(): void
    {
    }
}
