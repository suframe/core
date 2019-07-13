<?php
namespace suframe\core\commands\tcp;

use suframe\core\components\Config;
use suframe\core\components\console\SymfonyStyle;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;

class TcpCheckCommand extends TcpBase {

    /**
     * kill by shell: ps -ef |grep summer|cut -c 11-14 |xargs kill -9
     * @param InputInterface $input
     * @param OutputInterface $output
     * @return int|void|null
     * @throws \Exception
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $rs = $this->sendSig(0);
        $io = new SymfonyStyle($input, $output);
        if($rs !== true){
            $io->note('tcp has closed');
            return;
        }

        $config = Config::getInstance()->get('tcp.server')->toArray();
        $io->success(sprintf('tcp is running, listen: %s:%s', $config['listen'], $config['port']));
    }

    protected function configure()
    {
        $this->setName('tcp:check');
    }

}