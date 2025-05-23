<?php
declare(strict_types=1);

namespace Rochamarcelo\CakephpFieldsMapper;

use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Rochamarcelo\CakephpFieldsMapper\Data\EntityInfo;
use Rochamarcelo\CakephpFieldsMapper\Visitor\AddUseStmtVisitor;
use Rochamarcelo\CakephpFieldsMapper\Visitor\EntityFieldReplaceVisitor;

class TableProcessor
{
    /**
     * @param string $tableFilePath
     * @param \Rochamarcelo\CakephpFieldsMapper\Data\EntityInfo $entityInfo
     * @return string
     */
    public function process(string $tableFilePath, EntityInfo $entityInfo): string
    {
        if (empty($entityInfo->fields)) {
            throw new \UnexpectedValueException('No constant fields defined');
        }
        $content = file_get_contents($tableFilePath);
        $parser = (new ParserFactory())->createForHostVersion();
        $nodeTree = $parser->parse($content);
        if ($nodeTree === null) {
            throw new \UnexpectedValueException('Could not retrieve nodes from "' . $tableFilePath . '"');
        }
        $oldTokens = $parser->getTokens();
        $clonedNodes = $this->cloneTree($nodeTree);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new AddUseStmtVisitor($entityInfo->className));
        $traverser->addVisitor(new EntityFieldReplaceVisitor($entityInfo->fields));
        $updatedTree = $traverser->traverse($clonedNodes);
        $printer = new Standard();

        return $printer->printFormatPreserving($updatedTree, $nodeTree, $oldTokens);
    }

    /**
     * @param array $nodeTree
     * @return array
     */
    protected function cloneTree(array $nodeTree): array
    {
        $traverser = new NodeTraverser(
            new CloningVisitor(),
            new NameResolver(null, ['replaceNodes' => false,])
        );

        return $traverser->traverse($nodeTree);
    }
}