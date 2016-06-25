<?php

namespace FreezyBee\Magicrator\Grids;

use FreezyBee\Magicrator\IGenerator;
use Nette\Object;
use Nette\PhpGenerator\PhpNamespace;
use Nette\Utils\FileSystem;

/**
 * Class FormGenerator
 * @package FreezyBee\Magicrator\Grids
 */
class FactoryGenerator extends Object implements IGenerator
{
    /**
     * @param string $dir
     * @param string $componentNamespace
     * @param string $entityNamespace
     * @param string $facadeNamespace
     */
    public function generateToFile($dir, $componentNamespace, $entityNamespace = null, $facadeNamespace = null)
    {
        $dir = rtrim($dir, '/') . '/Factories';
        $name = substr($componentNamespace, strrpos($componentNamespace, '\\') + 1);

        FileSystem::write(
            $dir . '/I' . $name . 'Factory.php',
            "<?php\n\n" . $this->generate($componentNamespace, $entityNamespace, $facadeNamespace)
        );
    }

    /**
     * @param string $componentNamespace
     * @param string $entityNamespace
     * @param string $facadeNamespace
     * @return string
     */
    public function generate($componentNamespace, $entityNamespace = null, $facadeNamespace = null)
    {
        $phpNamespace = new PhpNamespace;
        $phpNamespace->setName(substr($componentNamespace, 0, strrpos($componentNamespace, '\\')) . '\Factories');

        $name = substr($componentNamespace, strrpos($componentNamespace, '\\') + 1);
        $name = 'I' . $name . 'Factory';

        $interface = $phpNamespace->addInterface($name);

        $interface
            ->addDocument('Interface ' . $name)
            ->addDocument('@package ' . $phpNamespace->getName())
            ->addMethod('create')
            ->setVisibility('public')
            ->addDocument('@return \\' . $componentNamespace);


        return preg_replace("/\t/", '    ', (string)$phpNamespace);
    }
}
