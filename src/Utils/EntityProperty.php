<?php

namespace FreezyBee\Magicrator\Utils;

use FreezyBee\Magicrator\Annotations;
use Nette\Object;
use Symfony\Component\Validator\Constraint;

/**
 * Class EntityProperty
 * @package FreezyBee\Magicrator\Utils
 */
class EntityProperty extends Object
{
    /**
     * @var string
     */
    public $name;
    
    /**
     * @var Annotations\All
     */
    public $annotation;
    
    /**
     * @var Constraint[]
     */
    public $asserts;

    /**
     * EntityProperty constructor.
     * @param string $name
     * @param Annotations\All $annotation
     * @param array $asserts
     */
    public function __construct($name, Annotations\All $annotation, array $asserts)
    {
        $this->annotation = $annotation;
        $this->asserts = $asserts;
        $this->name = $name;
    }
}
