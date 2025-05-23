<?php
declare(strict_types=1);

namespace Rochamarcelo\CakephpFieldsMapper\Command;

use Cake\Command\Command;
use Cake\Console\Arguments;
use Cake\Console\ConsoleIo;
use Cake\Utility\Inflector;
use Rochamarcelo\CakephpFieldsMapper\TableProcessor;

/**
 * FieldsMapper command.
 */
class FieldsMapperCommand extends Command
{
    /**
     * The name of this command.
     *
     * @var string
     */
    protected string $name = 'fields_mapper';

    /**
     * Get the default command name.
     *
     * @return string
     */
    public static function defaultName(): string
    {
        return 'fields_mapper';
    }

    /**
     * Implement this method with your command's logic.
     *
     * @param \Cake\Console\Arguments $args The command arguments.
     * @param \Cake\Console\ConsoleIo $io The console io
     * @return int|null|void The exit code or null for success
     */
    public function execute(Arguments $args, ConsoleIo $io)
    {
        $pathTables = APP . 'Model/Table/';
        $pathEntities = APP . 'Model/Entity/';

        $files = scandir($pathTables);
        $items = [];
        foreach ($files as $file) {
            if (preg_match('/^(.+)Table\.php$/', $file, $matches)) {
                $tableName = $matches[1];
                $entityName = Inflector::classify(Inflector::singularize($tableName));

                $entityFile = $pathEntities . $entityName . '.php';
                if (file_exists($entityFile)) {
                    $items[] = [
                        'pathTable' => $pathTables . $file,
                        'pathEntity' => $entityFile,
                    ];
                }
            }
        }

        if (empty($items)) {
            $io->out('No table models found in src/Model/Table/');
            return;
        }
        $processor = new TableProcessor();
        foreach ($items as $table) {
            $io->out('Processing file ' . $table['pathTable']);
            try {
                $output = $processor->process($table['pathTable'], $table['pathEntity']);
                file_put_contents($table['pathTable'], $output);
            } catch (\Exception $e) {
                $io->error($e->getMessage());
            }
        }
    }
}
