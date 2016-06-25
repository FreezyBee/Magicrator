<?php

namespace FreezyBee\Magicrator\Annotations;

use Doctrine\Common\Annotations\Annotation\Target;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class Form
{
    /** @var string */
    public $label;

    // TODO multiselect
    /** @Enum({"text", "password", "textArea", "upload", "checkbox", "select", "multiSelect", "cropper", "dateTime"}) */
    public $type = 'text';

    /** @var string */
    public $assocTitle = 'name';

    /** @var string */
    public $assocField;

    /** @var int */
    public $order = 66;
}
