<?php
/**
 * User: qian
 * Date: 2019/7/11 18:13
 */

namespace suframe\core\components\rpc;

use mysql_xdevapi\Exception;
use suframe\core\components\net\tcp\Proxy;
use suframe\core\components\swoole\Context;

class Client
{
    private static $instance = [];

    /**
     * @param $path
     * @return static
     */
    static function getInstance(string $path)
    {
        if (!isset(self::$instance[$path])) {
            self::$instance[$path] = new static($path);
        }
        return self::$instance[$path];
    }

    protected $path;
    protected $apiPath;

    public function __construct($path)
    {
        $this->path = $path;
        $path = explode('/', ltrim($path, '/'));
        array_shift($path);
        $this->apiPath = implode('/', $path);
    }

    public function __call($name, $arguments)
    {
        $data = [
            'arguments' => $arguments
        ];
        $data['path'] = '/rpc/' . $this->apiPath . '/' . $name;
        $request = Context::get('request');
        if($request && isset($request['get'])){
            $data['x_request_id'] = $request['get']['x_request_id'] ?? '';
        }
        $data = Proxy::getInstance()->sendData($this->path, json_encode($data));
        $rs = new RpcUnPack($data);
        if($rs->get('code') != 200){
            return null;
        }
        return $rs->get('data');
    }
}