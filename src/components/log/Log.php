<?php

namespace suframe\core\components\log;

use suframe\core\components\Config;
use suframe\core\components\rpc\SRpc;
use suframe\core\traits\Singleton;

class Log
{

    use Singleton;
    protected $api;

    public function __construct()
    {
        $this->api = Config::getInstance()->get('sapps.log');
    }

    /**
     * 请求日志
     * @param $request
     * @param string $mark
     * @return bool|void
     */
    public function request($request, $mark = '')
    {
        if(!$this->api){
            return false;
        }
        $this->write(LogConfig::TYPE_REQUEST, $request, $mark);
    }

    /**
     * rpc日志
     * @param $path
     * @param $params
     * @param $call
     * @param string $mark
     * @return bool|void
     */
    public function rpc($path, $params, $call, $mark = '')
    {
        if(!$this->api){
            return false;
        }
        $data = [
            'path' => $path,
            'params' => $params
        ];
        $this->write(LogConfig::TYPE_RPC, $data, $mark);
    }

    /**
     * sql日志
     * @param $sql
     * @param null $time
     * @param string $mark
     * @return bool|void
     */
    public function sql($sql, $time = null, $mark = '')
    {
        if(!$this->api){
            return false;
        }
        $data = [
            'sql' => $sql,
            'time' => $time
        ];
        $this->write(LogConfig::TYPE_SQL, $data, $mark);
    }

    /**
     * @param \Exception $e
     * @param string $mark
     * @return bool|void
     */
    public function exception(\Exception $e, $mark = '')
    {
        if(!$this->api){
            return false;
        }
        $data = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];
        $this->write(LogConfig::TYPE_SQL, $data, $mark);
    }

    public function debug($message, $data = [], $mark = '')
    {
        if(!$this->api){
            return false;
        }
        $data['message'] = $message;
        $this->write(LogConfig::TYPE_DEBUG, $data, $mark);
    }

    protected function write($type, $data, $mark = '')
    {
        go(function () use ($type, $data, $mark){
            return SRpc::route($this->api)->write($type, $data, $mark);
        });
    }

}