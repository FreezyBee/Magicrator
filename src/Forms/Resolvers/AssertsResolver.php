<?php

namespace FreezyBee\Magicrator\Forms\Resolvers;

use FreezyBee\Magicrator\Forms\Asserts\IAssert;
use Nette\Object;

/**
 * Class AssertsResolver
 * @package FreezyBee\Magicrator\Forms\Resolvers
 */
class AssertsResolver extends Object
{
    /**
     * @param $assertName
     * @param $data
     * @return IAssert
     */
    public static function getRuleCode($assertName, $data)
    {
        $class = 'FreezyBee\Magicrator\Forms\Asserts\\' . $assertName;

        if (class_exists($class)) {
            return new $class($data);
        }
        
        return null;
    }
}
