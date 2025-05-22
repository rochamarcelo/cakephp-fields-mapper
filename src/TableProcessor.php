<?php
declare(strict_types=1);

namespace Rochamarcelo\CakephpFieldsMapper;

use PhpParser\NodeTraverser;
use PhpParser\ParserFactory;
use PhpParser\PrettyPrinter\Standard;
use Rochamarcelo\CakephpFieldsMapper\Visitor\AddUseStmtVisitor;
use Rochamarcelo\CakephpFieldsMapper\Visitor\EntityFieldReplaceVisitor;

class TableProcessor
{
    /**
     * @param string $filePath The model path
     * @return void
     */
    public function process(string $filePath)
    {
        $fields = [
            'id' => ['constant' => 'FIELD_ID', 'entity' => 'User'],
            'username' => ['constant' => 'FIELD_USERNAME', 'entity' => 'User'],
            'email' => ['constant' => 'FIELD_EMAIL', 'entity' => 'User'],
            'password' => ['constant' => 'FIELD_PASSWORD', 'entity' => 'User'],
            'first_name' => ['constant' => 'FIELD_FIRST_NAME', 'entity' => 'User'],
            'last_name' => ['constant' => 'FIELD_LAST_NAME', 'entity' => 'User'],
            'token' => ['constant' => 'FIELD_TOKEN', 'entity' => 'User'],
        ];
        $content = file_get_contents($filePath);
        $parser = (new ParserFactory())->createForHostVersion();
        $nodeTree = $parser->parse($content);
        if ($nodeTree === null) {
            throw new \UnexpectedValueException('Could not retrieve nodes from "' . $filePath . '"');
        }
        $traverser = new NodeTraverser();
        $traverser->addVisitor(new AddUseStmtVisitor('App\Model\Entity\User'));
        $traverser->addVisitor(new EntityFieldReplaceVisitor($fields));
        $updatedTree = $traverser->traverse($nodeTree);
        $printer = new Standard();

        $newContent = $printer->prettyPrintFile($updatedTree);

        file_put_contents('output.php', $newContent);
    }
}