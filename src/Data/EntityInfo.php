<?php
declare(strict_types=1);

namespace Rochamarcelo\CakephpFieldsMapper\Data;
class EntityInfo
{
    /**
     * @param array<string, \Rochamarcelo\CakephpFieldsMapper\Data\EntityField> $fields
     * @param string|null $className
     * @param string|null $name
     */
    public function __construct(
        public array $fields,
        public ?string $className,
        public ?string $name,
    ) {

    }
}