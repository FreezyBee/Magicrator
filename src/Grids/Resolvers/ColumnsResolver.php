<?php

namespace FreezyBee\Magicrator\Grids\Resolvers;

use FreezyBee\Magicrator\Grids\Columns\IColumn;
use FreezyBee\Magicrator\Utils\EntityProperty;
use Nette\Object;

/**
 * Class InputsResolver
 * @package FreezyBee\Magicrator\Forms\Resolvers
 */
class ColumnsResolver extends Object
{
    /**
     * @param EntityProperty $property
     * @return IColumn
     * @throws \Exception
     */
    public static function getColumn(EntityProperty $property)
    {
        $class = 'FreezyBee\Magicrator\Grids\Columns\\' . ucfirst($property->annotation->type);

        if (class_exists($class)) {
            return new $class($property);
        } else {
            throw new \Exception('Unknown column type "' . $class . '" (' . $property->name . ')');
        }
    }
}
