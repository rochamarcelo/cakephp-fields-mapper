<?php
declare(strict_types=1);

namespace Rochamarcelo\CakephpFieldsMapper\Visitor;
use PhpParser\Node;
use PhpParser\Node\Identifier;
use PhpParser\Node\Name;
use PhpParser\NodeVisitorAbstract;

class EntityFieldReplaceVisitor extends NodeVisitorAbstract
{

    /**
     * @param array<string, \Rochamarcelo\CakephpFieldsMapper\Data\EntityField> $fieldsMap
     */
    public function __construct(private array $fieldsMap)
    {
    }

    /**
     * @param \PhpParser\Node $node
     * @return \PhpParser\Node\Expr\ClassConstFetch|null
     */
    public function enterNode(Node $node)
    {
        if ($node instanceof Node\Scalar\String_ && isset($this->fieldsMap[$node->value])) {
            return new Node\Expr\ClassConstFetch(
                new Name($this->fieldsMap[$node->value]->entityName),
                new Identifier($this->fieldsMap[$node->value]->const)
            );
        }

        return null;
    }
}