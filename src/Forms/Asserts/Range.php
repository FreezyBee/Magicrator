<?php

namespace FreezyBee\Magicrator\Forms\Asserts;

use \Symfony\Component\Validator\Constraints;

/**
 * Class Range
 * @package FreezyBee\Magicrator\Forms\Asserts
 */
class Range implements IAssert
{
    /**
     * @var Constraints\Range
     */
    private $assert;

    /**
     * NotBlank constructor.
     * @param $assert
     */
    public function __construct(Constraints\Range $assert)
    {
        $this->assert = $assert;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        $minMessage = str_replace('{{ limit }}', '%d', $this->assert->minMessage);
        $maxMessage = str_replace('{{ limit }}', '%d', $this->assert->maxMessage);

        $result = '';

        if ($this->assert->min !== null) {
            $result .= "\n    ->addRule(FreezyForm::MIN, '" . $minMessage . "', " . $this->assert->min . ")";
        }
        if ($this->assert->max !== null) {
            $result .= "\n    ->addRule(FreezyForm::MAX, '" . $maxMessage . "', " . $this->assert->max . ")";
        }
        
        return $result;
    }
}
