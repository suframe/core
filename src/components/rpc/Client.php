<?php
/**
 * User: qian
 * Date: 2019/7/11 18:13
 */

namespace suframe\core\components\rpc;

use suframe\core\components\net\http\Proxy;

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
        $arguments['path'] = '/rpc/' . $this->apiPath . '/' . $name;
        return Proxy::getInstance()->sendData($this->path, json_encode($arguments));
        // TODO: Implement __call() method.
    }
}