<?php

namespace suframe\core\components\log;

/**
 * 日志配置
 * Class LogConfig
 * @package suframe\core\components\log
 */
class LogConfig
{
    const TYPE_REQUEST = 'request';
    const TYPE_RPC = 'rpc';
    const TYPE_SQL = 'sql';
    const TYPE_EXCEPTION = 'exception';
    const TYPE_DEBUG = 'debug';
}