<?php
require __DIR__ . '/../vendor/autoload.php';

$extractor = new \Rochamarcelo\CakephpFieldsMapper\EntityInfoExtractor();
$processor = new \Rochamarcelo\CakephpFieldsMapper\TableProcessor();
$entityInfo = $extractor->extract(__DIR__ . '/src/Model/Entity/User.php');
$outputContent = $processor->process(
    __DIR__ . '/src/Model/Table/UsersTable.php',
    $entityInfo
);
file_put_contents('UsersTableNew.php', $outputContent);

