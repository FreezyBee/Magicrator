<?php

namespace FreezyBee\Magicrator\Forms\Inputs;

use FreezyBee\Magicrator\Utils\EntityProperty;
use Nette\Object;

/**
 * Class BaseInput
 * @package FreezyBee\Magicrator\Forms\Inputs
 */
class Container extends Object implements IInput
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
    public function __toString()
    {
        return '$' . $this->property->name . 'Container = $form->addContainer(\'' . $this->property->name . '\');';
    }
}
