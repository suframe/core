<?php

namespace suframe\core\commands\tcp;

use suframe\core\components\console\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TcpStopCommand extends TcpBase
{

    /**
     * kill by shell: ps -ef |grep summer|cut -c 11-14 |xargs kill -9
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $sig = $input->getParameterOption(['--sig', '-s'], SIGTERM);
        $rs = $this->sendSig($sig);
        $io = new SymfonyStyle($input, $output);
        if($rs !== true){
            $io->error($rs);
        } else {
            $io->success('tcp stop success');
        }
    }

    protected function configure()
    {
        $this->setName('tcp:stop')
            ->addOption('sig', 's', null, 'https://wiki.swoole.com/wiki/page/158.html');
    }

}