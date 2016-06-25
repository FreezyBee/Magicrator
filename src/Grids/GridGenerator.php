<?php

namespace FreezyBee\Magicrator\Grids;

use FreezyBee\Magicrator\Grids\Columns\IColumn;
use FreezyBee\Magicrator\Grids\Resolvers\ColumnsResolver;
use FreezyBee\Magicrator\IGenerator;
use FreezyBee\Magicrator\Utils\EntityInspector;
use FreezyBee\Magicrator\Utils\EntityProperty;
use Kdyby\Doctrine\EntityManager;
use Nette\Object;
use Nette\PhpGenerator\ClassType;
use Nette\PhpGenerator\PhpNamespace;
use Nette\Utils\FileSystem;

/**
 * Class GridGenerator
 * @package FreezyBee\Magicrator\Grids
 */
class GridGenerator extends Object implements IGenerator
{
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

        $componentName = self::getClassFromType($componentNamespace);

        $class = $this->phpNamespace->addClass($componentName);
        $class->setExtends('Nette\Application\UI\Control')
            ->addDocument('Class ' . $componentName)
            ->addDocument('@package ' . $this->phpNamespace->getName());


        $this->phpNamespace->addUse('Nette\Application\UI\Control');
        $this->phpNamespace->addUse('Ublaboo\DataGrid\DataGrid');
        $this->phpNamespace->addUse($facadeNamespace);

        $entityInpector = new EntityInspector($this->entityManager);
        $entityProperties = $entityInpector->inspectForGrid($this->entityNamespace);

        $this->generateProperties($class);
        $this->generateConstructor($class);
        $this->generateCreateGridFunction($class, $entityProperties);
        $this->generateDeleteHandler($class);
        $this->generateRender($class);

        return preg_replace("/\t/", '    ', (string)$this->phpNamespace);
    }

    /**
     * @param ClassType $class
     */
    public function generateProperties(ClassType &$class)
    {
        $class->addProperty($this->facadeName)
            ->setVisibility('private')
            ->addDocument('@var ' . ucfirst($this->facadeName));
    }

    /**
     * @param ClassType $class
     */
    public function generateConstructor(ClassType &$class)
    {
        $method = $class->addMethod('__construct');

        $method->addParameter($this->facadeName)
            ->setTypeHint($this->facadeNamespace);

        $method->addDocument('@param ' . ucfirst($this->facadeName) . ' $' . $this->facadeName);

        $method->addBody('$this->' . $this->facadeName . ' = $' . $this->facadeName . ';');
    }

    /**
     * @param ClassType $class
     * @param EntityProperty[] $entityProperties
     */
    public function generateCreateGridFunction(ClassType &$class, array $entityProperties)
    {
        $method = $class->addMethod('createComponentGrid');
        $method->setVisibility('protected')
            ->addDocument('@return DataGrid');

        // create form
        $method->addBody('$grid = new DataGrid;' . "\n");

        // set datasource
        $method->addBody('$grid->setDataSource($this->' . $this->facadeName . '->getRepository()' .
            "->createQueryBuilder('i'));\n");

        foreach ($entityProperties as $property) {
            /** @var IColumn $input */
            $column = ColumnsResolver::getColumn($property);
            $method->addBody((string)$column . "\n");
        }

        // action edit
        $method->addBody('$grid->addAction(\'edit\', \'\', \':\' . $this->presenter->getName() . \':edit\')' . "\n" .
            '    ->setIcon(\'pencil\');' . "\n");

        // action delete
        $method->addBody('$grid->addAction(\'delete\', \'\', \'delete\')' . "\n" . '    ->setIcon(\'trash\')' . "\n" .
            '    ->setConfirm(\'Opravdu to chcete smazat?\');' . "\n");

        // return grid
        $method->addBody('return $grid;');
    }

    /**
     * @param ClassType $class
     */
    public function generateDeleteHandler(ClassType &$class)
    {
        $method = $class->addMethod('handleDelete')
            ->addDocument('Delete handler');
        
        $method->addParameter('id');

        $method->addBody('$this->' . $this->facadeName . '->delete($id);');
    }

    /**
     * @param ClassType $class
     */
    public function generateRender(ClassType &$class)
    {
        $class->addMethod('render')
            ->setVisibility('public')
            ->addDocument('Renderer')
            ->addBody('$this[\'grid\']->render();');
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
