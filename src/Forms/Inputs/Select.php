<?php

namespace FreezyBee\Magicrator\Forms\Inputs;

use FreezyBee\Magicrator\Forms\Resolvers\AssertsResolver;

/**
 * Class CheckBox
 * @package FreezyBee\Magicrator\Forms\Inputs
 */
class Select extends BaseInput
{
    /**
     * @return string
     */
    protected function getInputName()
    {
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $result = '$form->addSelect'.
            '(\'' . $this->property->name . '\', \'' . $this->property->annotation->label . '\')';

        if ($this->property->annotation->assocField) {
            $result .= "\n    ->setOption(\\Kdyby\\DoctrineForms\\IComponentMapper::FIELD_NAME, '" .
                $this->property->annotation->assocField . "')";
        }

        $result .= "\n    ->setOption(\\Kdyby\\DoctrineForms\\IComponentMapper::ITEMS_TITLE, '" .
            $this->property->annotation->assocTitle . "')";


        foreach ($this->property->asserts as $assertName => $data) {
            $result .= AssertsResolver::getRuleCode($assertName, $data);
        }

        return $result . ';';
    }
}
