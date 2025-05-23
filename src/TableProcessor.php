<?php
declare(strict_types=1);

namespace Rochamarcelo\CakephpFieldsMapper;

use PhpParser\Lexer;
use PhpParser\Lexer\Emulative;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\NodeTraverser;
use PhpParser\NodeVisitor\CloningVisitor;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\Parser\Php7;
use PhpParser\Parser\Php8;
use PhpParser\ParserFactory;
use PhpParser\PhpVersion;
use PhpParser\PrettyPrinter\Standard;
use Rochamarcelo\CakephpFieldsMapper\Data\EntityField;
use Rochamarcelo\CakephpFieldsMapper\Data\EntityInfo;
use Rochamarcelo\CakephpFieldsMapper\Visitor\AddUseStmtVisitor;
use Rochamarcelo\CakephpFieldsMapper\Visitor\EntityFieldReplaceVisitor;

class TableProcessor
{
    /**
     * @param string $tableFilePath
     * @param string $entityFilePath
     * @return string
     */
    public function process(string $tableFilePath, string $entityFilePath): string
    {
        $content = file_get_contents($tableFilePath);
        $version = PhpVersion::getHostVersion();
        $lexer = $this->createLexer($version);
        $parser = $this->createParser($version, $lexer);
        $nodeTree = $parser->parse($content);
        if ($nodeTree === null) {
            throw new \UnexpectedValueException('Could not retrieve nodes from "' . $tableFilePath . '"');
        }
        $oldTokens = $parser->getTokens();
        $entityInfo = $this->extractEntityInfo($entityFilePath);
        $clonedNodes = $this->cloneTree($nodeTree);

        $traverser = new NodeTraverser();
        $traverser->addVisitor(new AddUseStmtVisitor($entityInfo->className));
        $traverser->addVisitor(new EntityFieldReplaceVisitor($entityInfo->fields));
        $updatedTree = $traverser->traverse($clonedNodes);
        $printer = new Standard();

        return $printer->printFormatPreserving($updatedTree, $nodeTree, $oldTokens);
    }

    /**
     * @param string $entityFilePath
     * @return \Rochamarcelo\CakephpFieldsMapper\Data\EntityInfo
     */
    protected function extractEntityInfo(string $entityFilePath): EntityInfo
    {
        $content = file_get_contents($entityFilePath);
        $nodes = (new ParserFactory())->createForHostVersion()->parse($content);
        $fields = [];
        $entityName = null;
        $entityClass = null;
        foreach ($nodes as $node) {
            if (!$node instanceof Namespace_) {
                continue;
            }
            $namespace = $node->name ? $node->name->toString() : '';
            foreach($node->stmts as $stmt) {
                if (!$stmt instanceof Class_) {
                    continue;
                }
                $entityName = $stmt->name->toString();
                $entityClass = $namespace.'\\'.$entityName;

                foreach ($stmt->stmts as $stmtClass) {
                    if (!$stmtClass instanceof ClassConst) {
                        continue;
                    }
                    foreach ($stmtClass->consts as $const) {
                        if (str_starts_with($const->name->toString(), 'FIELD_') && $const->value->value ?? null) {
                            $fields[(string)$const->value->value] = new EntityField($entityName, (string)$const->value->value, $const->name->toString());
                        }
                    }
                }
            }
        }

        return new EntityInfo($fields, $entityClass, $entityName);
    }

    /**
     * @param \PhpParser\PhpVersion $version
     * @return \PhpParser\Lexer
     */
    protected function createLexer(PhpVersion $version): Lexer
    {
        if ($version->isHostVersion()) {
            return new Lexer();
        }

        return new Emulative($version);
    }

    /**
     * @param \PhpParser\PhpVersion $version
     * @param \PhpParser\Lexer|\PhpParser\Lexer\Emulative $lexer
     * @return \PhpParser\Parser\Php7|\PhpParser\Parser\Php8
     */
    protected function createParser(PhpVersion $version, Lexer|Emulative $lexer): Php8|Php7
    {
        if ($version->id >= 80000) {
            return new Php8($lexer, $version);
        }
        return new Php7($lexer, $version);
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