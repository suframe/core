<?php

namespace suframe\core;

use suframe\core\components\log\Log;
use suframe\core\components\rpc\SRpc;

/**
 * 快捷入口
 * Class SF
 * @package suframe\core
 */
class SF
{
    /**
     * @param $path
     * @return SRpc
     */
    static public function rpc($path)
    {
        return SRpc::route($path);
    }

    /**
     * @return Log
     */
    static public function log()
    {
        return Log::getInstance();
    }

}