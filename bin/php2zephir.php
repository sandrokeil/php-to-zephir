<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/php-to-zephir for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/php-to-zephir/blob/master/LICENSE.md New BSD License
 */
namespace PhpToZephir;

use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;

// Setup/verify autoloading

if (file_exists($a = getcwd() . '/vendor/autoload.php')) {
    require $a;
} elseif (file_exists($a = __DIR__ . '/../../../autoload.php')) {
    require $a;
} elseif (file_exists($a = __DIR__ . '/../vendor/autoload.php')) {
    require $a;
} else {
    fwrite(STDERR, 'Cannot locate autoloader; please run "composer install"' . PHP_EOL);
    exit(1);
}
$argv = array_slice($argv, 1);
$command = array_shift($argv);
$help = <<<EOF
<info>Usage:</info>
  command [options] [arguments]
<info>Options:</info>
  <value>-h, --help, help</value>          Display this help message
<info>Available commands:</info>
  <value>convert</value>           Convert PHP file to Zephir
EOF;

$parser = (new ParserFactory)->create(ParserFactory::ONLY_PHP7);
$traverser = new NodeTraverser();
$zephirPrinter = new ZephirPrinter();

try {
    switch ($command) {
        case 'convert':
            $input = $argv[0];
            $output = $argv[1];
            $ast = $parser->parse(file_get_contents($input));
            $ast = $traverser->traverse($ast);

            $zep = $zephirPrinter->prettyPrintFile($ast);
            file_put_contents($output, $zep);

            exit(0);
        case '-h':
        case '--help':
        case 'help':
            echo $help;
            exit(0);
        default:
            echo $help;
            exit(1);
    }
} catch (\Throwable $e) {
    exit(1);
}