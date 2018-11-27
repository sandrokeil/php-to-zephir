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

class UnsetSplitter extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Unset_
            && count($node->vars) > 1
        ) {
            return array_map(function (Node\Expr $expr) {
                return new Node\Stmt\Unset_([$expr]);
            }, $node->vars);
        }
    }
}
