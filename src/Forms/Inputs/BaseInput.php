<?php

namespace FreezyBee\Magicrator\Forms\Inputs;

use FreezyBee\Magicrator\Forms\Resolvers\AssertsResolver;
use FreezyBee\Magicrator\Utils\EntityProperty;
use Nette\Object;

/**
 * Class BaseInput
 * @package FreezyBee\Magicrator\Forms\Inputs
 */
abstract class BaseInput extends Object implements IInput
{
    /** @var EntityProperty */
    protected $property;
    
    /**
     * BaseInput constructor.
     * @param EntityProperty $property
     */
    public function __construct(EntityProperty $property)
    {
        $this->property = $property;
    }

    /**
     * @return string
     */
    abstract protected function getInputName();

    /**
     * @return string
     */
    public function __toString()
    {
        $result = '$form->add' . $this->getInputName() .
            '(\'' . $this->property->name . '\', \'' . $this->property->annotation->label . '\')';

        foreach ($this->property->asserts as $assertName => $data) {
            $result .= AssertsResolver::getRuleCode($assertName, $data);
        }

        return $result . ';';
    }
}
