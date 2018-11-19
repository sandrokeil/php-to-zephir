<?php
/**
 * Sandro Keil (https://sandro-keil.de)
 *
 * @link      http://github.com/sandrokeil/php-to-zephir for the canonical source repository
 * @copyright Copyright (c) 2018 Sandro Keil
 * @license   http://github.com/sandrokeil/php-to-zephir/blob/master/LICENSE.md New BSD License
 */

declare(strict_types=1);

namespace PhpToZephir;

use PhpParser\Node;
use PhpParser\Node\Expr;
use PhpParser\Node\Stmt;

/**
 * Zephir Printer
 *
 * Overrides some PhpParser\PrettyPrinter\Standard methods to convert PHP code to Zephir zep files.
 *
 * Code is largely lifted from the PhpParser\PrettyPrinter\Standard implementation in
 * Nikic PhpParser, released with the copyright and license below.
 *
 * @see       https://github.com/nikic/PHP-Parser for the canonical source repository
 * @copyright Copyright (c) 2011-2018 by Nikita Popov
 * @license   https://github.com/nikic/PHP-Parser/blob/master/LICENSE New BSD License
 */
class ZephirPrinter extends \PhpParser\PrettyPrinter\Standard
{
    public function prettyPrintFile(array $stmts) : string
    {
        if (! $stmts) {
            return '';
        }

        $p = '' . $this->prettyPrint($stmts);

        if ($stmts[0] instanceof Stmt\InlineHTML) {
            $p = preg_replace('/^<\?php\s+\?>\n?/', '', $p);
        }
        if ($stmts[count($stmts) - 1] instanceof Stmt\InlineHTML) {
            $p = preg_replace('/<\?php$/', '', rtrim($p));
        }

        return $p;
    }

    protected function pStmt_Declare(Stmt\Declare_ $node)
    {
        $declares = $this->pCommaSeparated($node->declares);

        if (false !== strpos($declares, 'strict_types')) {
            return '';
        }

        return 'declare (' . $declares . ')'
            . (null !== $node->stmts ? ' {' . $this->pStmts($node->stmts) . $this->nl . '}' : ';');
    }

    protected function pExpr_Variable(Expr\Variable $node)
    {
        if ($node->name instanceof Expr) {
            return '${' . $this->p($node->name) . '}';
        } else {
            return $node->name;
        }
    }

    protected function pExpr_ArrayItem(Expr\ArrayItem $node)
    {
        return (null !== $node->key ? $this->p($node->key) . ': ' : '')
            . ($node->byRef ? '&' : '') . $this->p($node->value);
    }

    protected function pExpr_StaticPropertyFetch(Expr\StaticPropertyFetch $node)
    {
        return $this->pDereferenceLhs($node->class) . '::$' . $this->pObjectProperty($node->name);
    }

    protected function pVarLikeIdentifier(Node\VarLikeIdentifier $node)
    {
        return $node->name;
    }

    protected function pStmt_PropertyProperty(Stmt\PropertyProperty $node)
    {
        return $node->name
            . (null !== $node->default ? ' = ' . $this->p($node->default) : '');
    }

    protected function pStmt_Foreach(Stmt\Foreach_ $node)
    {
        return 'for ' . (null !== $node->keyVar ? $this->p($node->keyVar) . ', ' : '')
            . ($node->byRef ? '&' : '') . $this->p($node->valueVar) . ' in ' . $this->p($node->expr) . ' {'
            . $this->pStmts($node->stmts) . $this->nl . '}';
    }

    protected function pStmt_ClassConst(Stmt\ClassConst $node)
    {
        return 'const ' . $this->pCommaSeparated($node->consts) . ';';
    }

    protected function pImplode(array $nodes, string $glue = '') : string
    {
        $pNodes = [];
        foreach ($nodes as $node) {
            if (null === $node) {
                $pNodes[] = '';
            } elseif ($node instanceof Node\Param) {
                $param = $this->p($node);

                if ($node->type instanceof Node\Name) {
                    $param = explode(' ', $param);
                    $param = '<' . $param[0] .'> ' . $param[1];
                }
                $pNodes[] = $param;
            } else {
                $pNodes[] = $this->p($node);
            }
        }

        return implode($glue, $pNodes);
    }

    protected function pStmt_ClassMethod(Stmt\ClassMethod $node)
    {
        $returnType = null;

        if (null !== $node->returnType) {
            $returnType = $this->p($node->returnType);
        }

        if (null !== $node->returnType && $node->returnType->getType() === 'Name') {
            $returnType = '<' . $this->p($node->returnType) . '>';
        }

        if ($returnType === null) {
            foreach ($node->getComments() as $comment) {
                $matches = [];
                if (1 === preg_match('/(@return +)([\|\\a-z]+\n)/i', $comment->getText(), $matches)) {
                    $returnType = trim($matches[2]);

                    if ($returnType === 'mixed') {
                        $returnType = null;
                    }
                }
            }
        }

        $params = $node->getParams();

        foreach ($params as &$param) {
            if ($param->type === null) {
                $param->type = new Node\Identifier('var');
            }
        }

        return $this->pModifiers($node->flags)
            . 'function ' . ($node->byRef ? '&' : '') . $node->name
            . '(' . $this->pCommaSeparated($params) . ')'
            . (null !== $returnType ? ' -> ' . $returnType : '')
            . (null !== $node->stmts
                ? $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}'
                : ';') . "\n";
    }

    protected function pStmt_Function(Stmt\Function_ $node)
    {
        $returnType = '';

        if (null !== $node->returnType) {
            $returnType = $this->p($node->returnType);
        }

        if (null !== $node->returnType && $node->returnType->getType() === 'Name') {
            $returnType = '<' . $this->p($node->returnType) . '>';
        }

        return 'function ' . ($node->byRef ? '&' : '') . $node->name
            . '(' . $this->pCommaSeparated($node->params) . ')'
            . (null !== $node->returnType ? ' -> ' . $returnType : '')
            . $this->nl . '{' . $this->pStmts($node->stmts) . $this->nl . '}';
    }

    protected function pExpr_Assign(Expr\Assign $node)
    {
        return 'let ' . $this->pInfixOp(Expr\Assign::class, $node->var, ' = ', $node->expr);
    }

    protected function pSingleQuotedString(string $string)
    {
        if (strlen($string) === 1) {
            return '\'' . addcslashes($string, '\'\\') . '\'';
        }
        return '"' . addcslashes($string, '"\\') . '"';
    }
}
