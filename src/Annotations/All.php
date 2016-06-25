<?php

namespace FreezyBee\Magicrator\Annotations;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class All
{
    /** @var string */
    public $label;
    
    /** @Enum({"text", "checkbox", "dateTime"}) */
    public $type = 'text';

    /** @var string */
    public $assocTitle;

    /** @var string */
    public $assocField;
    
    /** @var int */
    public $order = 66;
}
