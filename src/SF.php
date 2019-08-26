<?php

namespace suframe\core;

use suframe\core\components\Config;
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
     * rpc接口
     * @param $path
     * @return SRpc
     */
    static public function rpc($path)
    {
        return SRpc::route($path);
    }

    /**
     * 日志
     * @return Log
     */
    static public function log()
    {
        return Log::getInstance();
    }

    /**
     * 配置
     * @return Config
     */
    static public function config()
    {
        return Config::getInstance();
    }

}