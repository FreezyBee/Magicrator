<?php

namespace FreezyBee\Magicrator\Forms\Resolvers;

use FreezyBee\Magicrator\Forms\Inputs\IInput;
use FreezyBee\Magicrator\Utils\EntityProperty;
use Nette\Object;

/**
 * Class InputsResolver
 * @package FreezyBee\Magicrator\Forms\Resolvers
 */
class InputsResolver extends Object
{
    /**
     * @param EntityProperty $property
     * @return IInput
     * @throws \Exception
     */
    public static function getInput(EntityProperty $property)
    {
        $class = 'FreezyBee\Magicrator\Forms\Inputs\\' . ucfirst($property->annotation->type);

        if (class_exists($class)) {
            return new $class($property);
        } else {
            throw new \Exception('Unknown column type "' . $class . '" (' . $property->name . ')');
        }
    }
}
