<?php

namespace FreezyBee\Magicrator\Annotations;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Grid
{
    /** @var string */
    public $label;

    /** @Enum({"text", "number", "dateTime", "link"}) */
    public $type = 'text';

    /** @var string */
    public $assocTitle;

    /** @var string */
    public $assocField;

    /** @var int */
    public $order = 66;
}
