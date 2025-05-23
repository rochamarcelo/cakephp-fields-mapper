<?php
declare(strict_types=1);

namespace Rochamarcelo\CakephpFieldsMapper;
use PhpParser\Node\Stmt\Class_;
use PhpParser\Node\Stmt\ClassConst;
use PhpParser\Node\Stmt\Namespace_;
use PhpParser\ParserFactory;
use Rochamarcelo\CakephpFieldsMapper\Data\EntityField;
use Rochamarcelo\CakephpFieldsMapper\Data\EntityInfo;

class EntityInfoExtractor
{
    /**
     * @param string $entityFilePath
     * @return \Rochamarcelo\CakephpFieldsMapper\Data\EntityInfo
     */
    public function extract(string $entityFilePath): EntityInfo
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
}