<?php

namespace FreezyBee\Magicrator\Console;

use FreezyBee\Magicrator\Forms;
use FreezyBee\Magicrator\Grids;
use FreezyBee\Magicrator\IGenerator;
use Kdyby\Doctrine\EntityManager;
use Nette\Neon\Neon;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

/**
 * Class MagicratorCommand
 * @package FreezyBee\Magicrator\Console
 */
class MagicratorCommand extends Command
{
    /**
     * @var OutputInterface
     */
    private $output;

    /**
     *
     */
    protected function configure()
    {
        $this->setName('magicrator:generate')
            ->setDescription('Generate forms and grids')
            ->addArgument(
                'filename',
                InputArgument::REQUIRED,
                'Filename'
            );
    }

    /**
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->output = $output;

        $filename = $input->getArgument('filename');

        if (!file_exists($filename)) {
            $output->writeln('<error>Wrong filename</error>');
            return 1;
        }

        $neon = Neon::decode(file_get_contents($filename));

        if ($neon == null) {
            $output->writeln('<error>Invalid neon file</error>');
            return 2;
        }

        $entityManager = $this->getHelper('container')->getByType(EntityManager::class);

        if (isset($neon['grids'])) {
            $this->output->writeln('<comment>Grids</comment>');

            $this->generate(
                $neon['grids'],
                new Grids\GridGenerator($entityManager),
                new Grids\FactoryGenerator
            );
        }

        if (isset($neon['forms'])) {
            $this->output->writeln('<comment>Forms</comment>');

            $this->generate(
                $neon['grids'],
                new Forms\FormGenerator($entityManager),
                new Forms\FactoryGenerator
            );
        }

        if (isset($neon['all'])) {
            $this->output->writeln('<comment>All</comment>');

            $gridGenerator = new Grids\GridGenerator($entityManager);
            $gridFactoryGenerator = new Grids\FactoryGenerator;

            $formGenerator = new Forms\FormGenerator($entityManager);
            $formFactoryGenerator = new Forms\FactoryGenerator;
            $baseFormGenerator = new Forms\BaseFormGenerator;

            foreach ($neon['all'] as $baseDir => $data) {
                $namespace = $data['namespace'];
                $items = $data['items'];

                foreach ($items as $item) {
                    $baseName = substr($item['entity'], strrpos($item['entity'], '\\') + 1);

                    $name = $namespace . '\\Grids\\' . $baseName . 'Grid';
                    $dir = $baseDir . '/Grids';
                    $gridGenerator->generateToFile($dir, $name, $item['entity'], $item['facade']);
                    $gridFactoryGenerator->generateToFile($dir, $name);
                    $this->output->writeln('<info>' . $name . ' generated</info>');

                    $name = $namespace . '\\Forms\\' . $baseName . 'Form';
                    $dir = $baseDir . '/Forms';
                    $formGenerator->generateToFile($dir, $name, $item['entity'], $item['facade']);
                    $formFactoryGenerator->generateToFile($dir, $name);
                    $baseFormGenerator->generateToFile($dir, $name);
                    $this->output->writeln('<info>' . $name . ' generated</info>');
                }
            }
        }

        return 0;
    }

    /**
     * @param array $type
     * @param IGenerator $componentGenerator
     * @param IGenerator $factoryGenerator
     */
    private function generate(array $type, IGenerator $componentGenerator, IGenerator $factoryGenerator)
    {
        foreach ($type as $dir => $items) {
            foreach ($items as $name => $data) {
                $componentGenerator->generateToFile($dir, $name, $data['entity'], $data['facade']);
                $factoryGenerator->generateToFile($dir, $name, '', '');
                $this->output->writeln('<info>' . $name . ' generated</info>');

                if ($componentGenerator instanceof Forms\FormGenerator) {
                    $baseFormGenerator = new Forms\BaseFormGenerator;
                    $baseFormGenerator->generateToFile($dir, $name);
                }
            }
        }
    }
}
