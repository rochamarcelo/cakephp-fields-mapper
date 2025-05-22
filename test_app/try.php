<?php
require __DIR__ . '/../vendor/autoload.php';

$processor = new \Rochamarcelo\CakephpFieldsMapper\TableProcessor();
$processor->process(__DIR__ . '/src/Model/Table/UsersTable.php');