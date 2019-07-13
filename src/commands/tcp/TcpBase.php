<?php
namespace suframe\core\commands\tcp;

use suframe\core\components\swoole\ProcessTools;
use Symfony\Component\Console\Command\Command;

abstract class TcpBase extends Command
{
    protected function sendSig($sig){
        return ProcessTools::kill($sig);
    }
}