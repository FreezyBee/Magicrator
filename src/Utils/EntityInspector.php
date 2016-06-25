<?php

namespace FreezyBee\Magicrator\Utils;

use Doctrine\Common\Annotations\AnnotationReader;
use FreezyBee\Magicrator\Annotations;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;

/**
 * Class EntityInspector
 * @package FreezyBee\Magicrator\Utils
 */
class EntityInspector extends Object
{
    /** Form inspection type */
    const TYPE_FORM = Annotations\Form::class;

    /** Grid inspection type */
    const TYPE_GRID = Annotations\Grid::class;

    /** All inspection type */
    const TYPE_ALL = Annotations\All::class;

    /**
     * @var string
     */
    private static $selType;

    /** @var EntityManager */
    private $entityManager;

    /** @var AnnotationReader */
    private $annotationReader;

    /**
     * EntityInspector constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
        $this->annotationReader = new AnnotationReader;
    }

    /**
     * @param string $entityNamespace
     * @return EntityProperty[]
     */
    public function inspect($entityNamespace)
    {
        $metadata = $this->entityManager->getClassMetadata($entityNamespace);

        $properties = [];

        // inspect Columns
        foreach ($metadata->getFieldNames() as $propertyName) {
            $reflectionProperty = new \ReflectionProperty($entityNamespace, $propertyName);

            $magicratorAnnotation = $this->getMagicratorAnnotations($reflectionProperty);
            if ($magicratorAnnotation == null) {
                continue;
            }

            $asserts = [];

            // asserts
            if (self::$selType == self::TYPE_FORM) {
                foreach ($this->annotationReader->getPropertyAnnotations($reflectionProperty) as &$annotation) {
                    if (preg_match(
                        '/Symfony\\\\Component\\\\Validator\\\\Constraints\\\\(.*)/',
                        get_class($annotation),
                        $match
                    )) {
                        $asserts[$match[1]] = $annotation;
                    }
                }
            }

            $properties[$propertyName] = new EntityProperty($propertyName, $magicratorAnnotation, $asserts);
        }

        // inspect associations
        foreach ($metadata->getAssociationNames() as $propertyName) {
            $reflectionProperty = new \ReflectionProperty($entityNamespace, $propertyName);

            $annotation = $this->getMagicratorAnnotations($reflectionProperty);
            if ($annotation == null) {
                continue;
            }

            $manyToOne = $this->annotationReader
                ->getPropertyAnnotation($reflectionProperty, 'Doctrine\ORM\Mapping\ManyToOne');

            $oneToOne = $this->annotationReader
                ->getPropertyAnnotation($reflectionProperty, 'Doctrine\ORM\Mapping\OneToOne');

            $oneToMany = $this->annotationReader
                ->getPropertyAnnotation($reflectionProperty, 'Doctrine\ORM\Mapping\OneToMany');

            $manyToMany = $this->annotationReader
                ->getPropertyAnnotation($reflectionProperty, 'Doctrine\ORM\Mapping\ManyToMany');

            // TODO multiselect
            if ($annotation->type == 'text' && self::$selType == self::TYPE_FORM) {
                if ($manyToOne) {
                    $annotation->type = 'select';
                } elseif ($oneToOne) {
                    $annotation->type = 'container';
                } elseif ($oneToMany) {
                    $annotation->type = 'multiSelect';
                } elseif ($manyToMany) {
                    $annotation->type = 'multiSelect';
                } else {
                    throw new \Exception('wtf?');
                }
            }

            // TODO fix cropper
            if ($annotation->type == 'cropper') {
                $propertyName .= 'X';
            }

            $properties[$propertyName] = new EntityProperty($propertyName, $annotation, []);
        }
        
        usort($properties, function (EntityProperty $a, EntityProperty $b) {
            return $a->annotation->order > $b->annotation->order;
        });

        return $properties;
    }

    /**
     * @param \ReflectionProperty $reflectionProperty
     * @return Annotations\All|null
     */
    private function getMagicratorAnnotations(\ReflectionProperty $reflectionProperty)
    {
        /** @var Annotations\All $annotAll */
        $annotAll = $this->annotationReader->getPropertyAnnotation($reflectionProperty, self::TYPE_ALL);

        /** @var Annotations\Grid|Annotations\Form $annotExact */
        $annotExact = $this->annotationReader->getPropertyAnnotation($reflectionProperty, self::$selType);

        // merge
        if ($annotAll || $annotExact) {
            if ($annotAll == null) {
                $annotAll = new Annotations\All;
            }

            if ($annotExact != null) {
                $annotAll->label = $annotExact->label;
                $annotAll->type = $annotExact->type;
                $annotAll->assocTitle = $annotExact->assocTitle;
                $annotAll->assocField = $annotExact->assocField;
                $annotAll->order = $annotExact->order;
            }

            return $annotAll;

        } else {
            return null;
        }
    }

    /**
     * @param $entityClassName
     * @return EntityProperty[]
     * @throws \Exception
     */
    public function inspectForForm($entityClassName)
    {
        self::$selType = self::TYPE_FORM;
        return $this->inspect($entityClassName);
    }


    /**
     * @param $entityClassName
     * @return EntityProperty[]
     * @throws \Exception
     */
    public function inspectForGrid($entityClassName)
    {
        self::$selType = self::TYPE_GRID;
        return $this->inspect($entityClassName);
    }
}
