<?php

namespace FreezyBee\Magicrator\Grids\Columns;

use FreezyBee\Magicrator\Utils\EntityProperty;
use Nette\Object;

/**
 * Class BaseInput
 * @package FreezyBee\Magicrator\Forms\Inputs
 */
abstract class BaseColumn extends Object implements IColumn
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
    abstract protected function getColumnName();

    /**
     * @return string
     */
    public function __toString()
    {
        $assocTitle = $this->property->annotation->assocTitle;
        $name = $this->property->name;

        return '$grid->addColumn' . $this->getColumnName() .
        '(\'' . $name . '\', \'' . $this->property->annotation->label . '\'' .
        ($assocTitle ? ', \'' . $name . '.' . $assocTitle . '\'' : '') .
        ');';
    }
}
