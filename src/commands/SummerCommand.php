<?php
namespace suframe\core\commands;

use suframe\core\components\Config;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Helper\Helper;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SummerCommand  extends Command{

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        if (true === $input->hasParameterOption(['--version', '-v'], true)) {
            return $output->writeln('0.0.1');
        }
        $config = Config::getInstance();
        $style = new SymfonyStyle($input, $output);
        $style->block(
            $config->get('console.logoText'),
            null,
            $config->get('console.logoStyle')
        );

        $style->text(sprintf('<comment>%s</comment>', 'Commands:'));
        $style->text(sprintf('<info>%s</info>     %s', 'help', 'Displays help for a command'));
        $style->text(sprintf('<info>%s</info>     %s', 'list', 'Lists commands'));
    }

    protected function configure()
    {
        $this->setName('summer')
            ->setDescription('default command')
            ->addOption('version', 'v', null, 'summer framework version')
        ;
    }
}