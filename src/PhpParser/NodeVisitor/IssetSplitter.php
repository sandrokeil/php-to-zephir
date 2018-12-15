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
use PhpToZephir\Exception\RuntimeException;

class IssetSplitter extends NodeVisitorAbstract
{
    public function leaveNode(Node $node)
    {
        if ($node instanceof Node\Stmt\If_
            && $node->cond instanceof Node\Expr\Isset_
        ) {
            if ($and = $this->issetCondition($node->cond)) {
                return new Node\Stmt\If_($and[0]);
            }
        }
        if ($node instanceof Node\Stmt\If_
            && $node->cond instanceof Node\Expr\BinaryOp
            && $node->cond->right instanceof Node\Expr\Isset_
        ) {
            if ($and = $this->issetCondition($node->cond->right)) {
                $node->cond->right = $and[0];
            }
        }
    }

    private function issetCondition(Node\Expr\Isset_ $node): ?array
    {
        if (count($node->vars) > 1) {
            $issetConditions = array_map(function (Node\Expr $expr) {
                return new Node\Expr\Isset_([$expr]);
            }, $node->vars);
            $number = count($issetConditions);

            if ($number > 2) {
                throw new RuntimeException('Only two variables are supported for isset conversion.');
            }

            $and = [];
            for ($i = 0; $i < $number; $i += 2) {
                $and[] = new Node\Expr\BinaryOp\BooleanAnd($issetConditions[$i], $issetConditions[$i + 1]);
            }
            return $and;
        }
        return null;
    }
}
