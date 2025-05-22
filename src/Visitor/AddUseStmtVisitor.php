<?php
declare(strict_types=1);

namespace Rochamarcelo\CakephpFieldsMapper\Visitor;
use PhpParser\BuilderFactory;
use PhpParser\Node;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\Node\Stmt\Use_;
use PhpParser\NodeVisitor;
use PhpParser\NodeVisitorAbstract;

class AddUseStmtVisitor extends NodeVisitorAbstract
{
    /**
     * @var true
     */
    private bool $exists = false;

    private array $useNodes = [];
    private bool $replaced = false;

    /**
     * @param string $className
     */
    public function __construct(private string $className)
    {
    }

    /**
     * @param array $nodes
     * @return null
     */
    public function beforeTraverse(array $nodes)
    {
        $this->useNodes = [];
        $this->exists = false;
        $this->replaced = false;

        return null;
    }

    /**
     * @param \PhpParser\Node $node
     * @return null
     */
    public function enterNode(Node $node)
    {
        if (!$node instanceof Use_) {
            return null;
        }
        foreach ($node->uses as $use) {
            if ($use->name->toString() === $this->className) {
                $this->exists = true;
            }
            $this->useNodes[] = $use;
        }
        return NodeVisitor::REMOVE_NODE;
    }

    /**
     * @param \PhpParser\Node $node
     * @return null
     */
    public function leaveNode(Node $node)
    {
        if ($node instanceof Namespace_ && !$this->replaced) {
            if (!$this->exists) {
                $stmt = (new BuilderFactory())->use($this->className)->getNode()->uses[0];
                $this->useNodes[] = $stmt;
            }
            $items = [];
            foreach ($this->useNodes as $use) {
                $items[$use->name->toString()] = $use;
            }
            ksort($items);
            $items = array_values($items);
            $useStmts = array_map(fn($item) => new Use_([$item]), $items);
            $node->stmts = array_merge($useStmts, $node->stmts);
            $this->replaced = true;
        }

        return null;
    }
}