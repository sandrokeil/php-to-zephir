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

use PhpToZephir\Exception\FileNotExistsException;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\ProgressBar;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use RecursiveDirectoryIterator;
use RecursiveIteratorIterator;
use RecursiveRegexIterator;
use RegexIterator;

abstract class AbstractCommand extends Command
{
    /**
     * @var string
     */
    protected $from;

    /**
     * @var string
     */
    protected $to;

    /**
     * @var string
     */
    protected $isDir;

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->from = $input->getArgument('from');
        $this->to = $input->getArgument('to');
        $this->isDir = is_dir($this->from);

        if (! file_exists($this->from)) {
            throw FileNotExistsException::forFile($this->from);
        }
        $this->from = realpath($this->from);

        $progressBar = new ProgressBar($output, $this->countFiles($this->from));
        $i = 0;

        foreach ($this->getFileContent($this->from) as $fileInfo) {
            $this->processFileContent($fileInfo[0], $fileInfo[1]);
            $i++;
            $progressBar->setProgress($i);
        }
        $progressBar->finish();
        $this->finished();
        $output->writeln('');
    }

    abstract protected function processFileContent(string $file, string $fileContent): void;

    abstract protected function finished(): void;

    private function getFileContent(string $from): \Generator
    {
        if (is_file($from)) {
            yield [$from, file_get_contents($from)];
            return;
        }

        $directory = new RecursiveDirectoryIterator($from);
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

        $regex->rewind();

        foreach ($regex as $file) {
            yield [$file[0], file_get_contents($file[0])];
        }
    }

    private function countFiles(string $from)
    {
        if (is_file($from)) {
            return 1;
        }

        $directory = new RecursiveDirectoryIterator($from);
        $iterator = new RecursiveIteratorIterator($directory);
        $regex = new RegexIterator($iterator, '/^.+\.php$/i', RecursiveRegexIterator::GET_MATCH);

        return iterator_count($regex);
    }
}
