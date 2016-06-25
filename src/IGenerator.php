<?php

namespace FreezyBee\Magicrator;

/**
 * Interface IGenerator
 * @package FreezyBee\Magicrator
 */
interface IGenerator
{
    /**
     * @param string $componentNamespace
     * @param string $entityNamespace
     * @param string $facadeNamespace
     * @return string
     */
    public function generate($componentNamespace, $entityNamespace, $facadeNamespace);

    /**
     * @param string $dir
     * @param string $componentNamespace
     * @param string $entityNamespace
     * @param string $facadeNamespace
     */
    public function generateToFile($dir, $componentNamespace, $entityNamespace, $facadeNamespace);
}
