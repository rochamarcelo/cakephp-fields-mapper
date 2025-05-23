<?php
declare(strict_types=1);

namespace Rochamarcelo\CakephpFieldsMapper\Data;
class EntityField
{
    /**
     * @param string $entityName
     * @param string $fieldName
     * @param string $const
     */
    public function __construct(
        public string $entityName,
        public string $fieldName,
        public string $const,
    )
    {
    }
}