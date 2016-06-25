<?php

namespace FreezyBee\Magicrator\Forms\Asserts;

use \Symfony\Component\Validator\Constraints;

/**
 * Class NotBlank
 * @package FreezyBee\Magicrator\Forms\Asserts
 */
class NotBlank implements IAssert
{
    /**
     * @var Constraints\NotBlank
     */
    private $assert;

    /**
     * NotBlank constructor.
     * @param $assert
     */
    public function __construct(Constraints\NotBlank $assert)
    {
        $this->assert = $assert;
    }

    /**
     * @return string
     */
    public function __toString()
    {
        return "\n    ->setRequired('" . $this->assert->message .  "')";
    }
}
