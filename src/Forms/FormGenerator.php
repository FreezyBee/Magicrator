<?php

namespace FreezyBee\Magicrator\Forms;

use FreezyBee\Magicrator\Forms\Inputs\IInput;
use FreezyBee\Magicrator\Forms\Resolvers\InputsResolver;
use FreezyBee\Magicrator\IGenerator;
use FreezyBee\Magicrator\Utils\EntityInspector;
use FreezyBee\Magicrator\Utils\EntityProperty;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Nette\Utils\FileSystem;

/**
 * Class FormGenerator
 * @package FreezyBee\Magicrator\Forms\FormGenerator
 */
class FormGenerator extends Object implements IGenerator
{
    /**  */
    const TEXT = 0;

    /**  */
    const PASSWORD = 1;

    /**  */
    const TEXT_AREA = 2;

    /**  */
    const CHECKBOX = 3;

    /**  */
    const SELECT = 4;

    /**  */
    const MULTI_SELECT = 5;

    /** @var EntityManager */
    private $entityManager;

    /** @var PhpNamespace */
    private $phpNamespace;

    /** @var string */
    private $facadeName;

    /** @var string */
    private $facadeNamespace;

    /** @var string */
    private $entityNamespace;

    /** @var array */
    private $dependencies;

    /**
     * FormGenerator constructor.
     * @param EntityManager $entityManager
     */
    public function __construct(EntityManager $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    /**
     * @param string $dir
     * @param string $componentNamespace
     * @param string $entityNamespace
     * @param string $facadeNamespace
     */
    public function generateToFile($dir, $componentNamespace, $entityNamespace, $facadeNamespace)
    {
        $dir = rtrim($dir, '/');
        $name = self::getClassFromType($componentNamespace);

        FileSystem::write(
            $dir . '/' . $name . '.php',
            "<?php\n\n" . $this->generate($componentNamespace, $entityNamespace, $facadeNamespace)
        );
    }

    /**
     * @param string $componentNamespace
     * @param string $entityNamespace
     * @param string $facadeNamespace
     * @return string
     */
    public function generate($componentNamespace, $entityNamespace, $facadeNamespace)
    {
        $this->phpNamespace = new PhpNamespace();
        $this->phpNamespace->setName(substr($componentNamespace, 0, strrpos($componentNamespace, '\\')));

        $this->entityNamespace = $entityNamespace;
        $this->facadeNamespace = $facadeNamespace;

        $this->facadeName = lcfirst(self::getClassFromType($facadeNamespace));

        $this->dependencies = [
            'FreezyBee\Forms\Services\FormService' => 'formService',
            $this->facadeNamespace => $this->facadeName
        ];

        $componentName = self::getClassFromType($componentNamespace);
        
        $class = $this->phpNamespace->addClass($componentName);
        $class->setExtends($this->phpNamespace->getName() . '\BaseForm')
            ->addDocument('Class ' . $componentName)
            ->addDocument('@package ' . $this->phpNamespace->getName());

        $this->phpNamespace->addUse('FreezyBee\Forms\FreezyForm');
        $this->phpNamespace->addUse('FreezyBee\Forms\Rendering\Bs3FormRenderer');
        $this->phpNamespace->addUse($entityNamespace);

        foreach ($this->dependencies as $type => $property) {
            if ($type) {
                $this->phpNamespace->addUse($type);
            }
        }

        $entityInpector = new EntityInspector($this->entityManager);
        $entityProperties = $entityInpector->inspectForForm($this->entityNamespace);


        $this->generateProperties($class);
        $this->generateConstructor($class);
        $this->generateCreateComponentForm($class, $entityProperties);

        return preg_replace("/\t/", '    ', (string)$this->phpNamespace);
    }

    /**
     * @param ClassType $class
     */
    public function generateProperties(ClassType &$class)
    {
        $class->addProperty($this->facadeName)
            ->setVisibility('private')
            ->addDocument('@var ' . self::getClassFromType($this->facadeNamespace));
    }

    /**
     * @param ClassType $class
     */
    public function generateConstructor(ClassType &$class)
    {
        $method = $class->addMethod('__construct');

        $method->addBody('parent::__construct($formService);' . "\n");
        
        foreach ($this->dependencies + ['id'] as $type => $property) {
            $method->addParameter($property)
                ->setTypeHint($type);

            $method->addDocument('@param ' . ($type ? self::getClassFromType($type) : '')  . ' $' . $property);

            if ($property != 'formService') {
                $method->addBody('$this->' . $property . ' = $' . $property . ';');
            }
        }

        $method->addBody(
            "\nif (\$id) {\n    \$this->entity = \$" . $this->facadeName . "->find(\$id);\n}\n\n" .
            "if (\$this->entity == null) {\n    \$this->entity = new " . self::getClassFromType($this->entityNamespace).
            ";\n}"
        );
    }

    /**
     * @param ClassType $class
     * @param EntityProperty[] $entityProperties
     */
    public function generateCreateComponentForm(ClassType &$class, array $entityProperties)
    {
        $method = $class->addMethod('createComponentForm');
        $method->setVisibility('protected')
            ->addDocument('@return FreezyForm');

        // create form
        $method->addBody('$form = new FreezyForm;' . "\n");

        foreach ($entityProperties as $property) {
            /** @var IInput $input */
            $input = InputsResolver::getInput($property);
            $method->addBody((string)$input . "\n");
        }

        // button
        $method->addBody('$form->addSubmit(\'save\', \'Uložit\');' . "\n");

        // load defaults
        $method->addBody('$this->formService->loadFormDefaults($this->entity, $form);' . "\n");

        // set renderer
        $method->addBody('$form->setRenderer(new Bs3FormRenderer);' . "\n");

        // register callback
        $method->addBody('$form->onSuccess[] = [$this, \'process\'];' . "\n");

        // return form
        $method->addBody('return $form;');
    }

    /**
     * @param $type
     * @return mixed
     */
    private static function getClassFromType($type)
    {
        $pos = strrpos($type, '\\');
        return ($pos !== false) ? substr($type, $pos + 1) : $type;
    }
}
