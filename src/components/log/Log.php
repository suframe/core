<?php

namespace suframe\core\components\log;

use suframe\core\components\rpc\SRpc;
use suframe\core\traits\Singleton;

class Log
{

    use Singleton;
    protected $api;

    public function __construct($api)
    {
        $this->api = $api;
    }

    public function request($request, $mark = '')
    {
        return $this->write(LogConfig::TYPE_REQUEST, $request, $mark);
    }

    public function rpc($path, $params, $call, $mark = '')
    {
        $data = [
            'path' => $path,
            'params' => $params,
            //调用栈
            'call' => [
                'path' => '',
                'file' => '',
                'line' => '',
            ],
        ];
        return $this->write(LogConfig::TYPE_RPC, $request, $mark);
    }

    public function sql($sql, $time = null, $mark = '')
    {
        $data = [
            'sql' => $sql,
            'time' => $time
        ];
        return $this->write(LogConfig::TYPE_SQL, $data, $mark);
    }

    public function exception(\Exception $e, $mark = '')
    {
        $data = [
            'message' => $e->getMessage(),
            'code' => $e->getCode(),
            'file' => $e->getFile(),
            'line' => $e->getLine(),
            'trace' => $e->getTraceAsString(),
        ];
        return $this->write(LogConfig::TYPE_SQL, $data, $mark);
    }

    public function debug($message, $data = [], $mark = '')
    {
        $data['message'] = $message;
        return $this->write(LogConfig::TYPE_DEBUG, $data, $mark);
    }

    protected function write($type, $data, $mark = '')
    {
        return SRpc::route($this->api)->write($type, $data, $mark);
    }

}