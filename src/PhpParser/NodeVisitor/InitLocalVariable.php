<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/php-to-zephir for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/php-to-zephir/blob/master/LICENSE.md New BSD License
 */
declare(strict_types=1);

namespace PhpToZephir\PhpParser\NodeVisitor;

use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

/**
 * Add a variable node without attributes to initialize the local variable for Zephir.
 */
class InitLocalVariable extends NodeVisitorAbstract
{
    /**
     * @var array
     */
    private $initialized = [];

    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Expr\Assign && $node->var instanceof Node\Expr\Variable) {
            $name = $node->var->name;
            $this->initialized[$name] = $name;
        }
    }

    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\ClassMethod) {
            $stmts = $node->stmts;

            foreach ($this->initialized as $name) {
                array_unshift($stmts, new Node\Expr\Variable($name));
            }
            $node->stmts = $stmts;
            $this->initialized = [];
        }
    }
}
