<?php

namespace FreezyBee\Magicrator\DI;

use Kdyby\Console\DI\ConsoleExtension;
use Nette\DI\CompilerExtension;

/**
 * Class MagicratorExtension
 * @package FreezyBee\Magicrator\DI
 */
class MagicratorExtension extends CompilerExtension
{
    public function loadConfiguration()
    {
        $builder = $this->getContainerBuilder();

        $def = $builder->addDefinition($this->prefix('command.magicrator'));
        $def->setClass('FreezyBee\Magicrator\Console\MagicratorCommand');

        $def->setAutowired(false);
        $def->setInject(false);
        $def->addTag(ConsoleExtension::TAG_COMMAND);
    }
}
