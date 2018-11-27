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

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitorAbstract;
use PhpParser\Node;

class RemoveUseFunction extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Use_
            && ($node->type === Node\Stmt\Use_::TYPE_FUNCTION || $node->type === Node\Stmt\Use_::TYPE_CONSTANT)
        ) {
            return NodeTraverser::REMOVE_NODE;
        }
    }
}
