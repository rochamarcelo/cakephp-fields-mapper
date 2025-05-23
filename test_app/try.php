<?php
require __DIR__ . '/../vendor/autoload.php';

$processor = new \Rochamarcelo\CakephpFieldsMapper\TableProcessor();
$outputContent = $processor->process(__DIR__ . '/src/Model/Table/UsersTable.php', __DIR__ . '/src/Model/Entity/User.php');
file_put_contents('UsersTableNew.php', $outputContent);

