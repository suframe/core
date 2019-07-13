<?php
namespace suframe\core\components\swoole;

use suframe\core\components\Config;
use swoole_process;

class ProcessTools{

    /**
     * @param $pidFile
     * @param $sig
     * @return string
     */
    static public function kill($sig = SIGUSR1){
        $config = Config::getInstance();
        $pidFile = $config->get('tcp.swoole.pid_file');
        if(!is_file($pidFile)){
            return false;
        }
        $pid = file_get_contents($pidFile);
        if (!@swoole_process::kill($pid, $sig)) {
            return false;
        }
    }

}