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

class ArrayDestructuring extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\Expression
            && $node->expr instanceof Node\Expr\Assign
            && $node->expr->expr instanceof Node\Expr\Array_
            && $node->expr->var instanceof Node\Expr\Array_
        ) {
            $stmts = array_map(function (Node\Expr\ArrayItem $item) {
                return new Node\Expr\Variable($item->value->name);
            }, $node->expr->var->items);

            $arrayName = bin2hex(random_bytes(6));
            $key = -1;
            $destructuring = array_map(function (Node\Expr\ArrayItem $item) use ($stmts, $arrayName, &$key) {
                $key++;
                $name = clone $stmts[$key];
                $name->setAttribute('init', false);
                return new Node\Expr\Assign($name, new Node\Expr\ArrayDimFetch(
                    new Node\Expr\Variable($arrayName, $item->getAttributes()),
                    new Node\Scalar\LNumber($key)
                ));
            }, $node->expr->var->items);

            $node->expr->var = new Node\Expr\Variable($arrayName, $node->getAttributes());

            $stmts[] = $node;

            $stmts = array_merge($stmts, $destructuring);

            return $stmts;
        }
    }
}
