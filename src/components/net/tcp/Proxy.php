<?php
/**
 * User: qian
 * Date: 2019/6/5 11:42
 */
namespace suframe\core\components\net\tcp;

use suframe\core\components\register\Client;
use suframe\core\traits\Singleton;
use Swoole\Http\Request;

/**
 * tcp 代理
 * Class HttpProxy
 * @package suframe\proxy\components
 */
class Proxy {
    use Singleton;

    protected $counter = 0;
    protected $resultError = 0;
    protected $pools;
    protected $config;

    public function __construct() {
        echo "new proxy created\n";
        $this->config = Client::getInstance()->reloadServer()['servers'];
        $this->initPools();
    }

    protected function initPools(){
        foreach ($this->config as $path => $item) {
            $this->addPool($path, $item);
        }
    }

    public function sendData($path, $data = '')
    {
        //为了效率，避免一层层的遍历，目前只支持1级目录代理
        $router = explode('/', ltrim($path, '/'));
        $router = array_shift($router);
        $pool = $this->getPool($router);
        if ($pool) {
            $client = $pool->get();
            if ($client) {
                //链接端口可能会出警告
                $ret = $client->send($data);
                if ($ret) {
                    //无法判断tcp 因为应用层无法获得底层TCP连接的状态，执行send或recv时应用层与内核发生交互，才能得到真实的连接可用状态
                    $rs = @$client->recv();
                    if ($rs) {
                        $pool->put($client);
                        return $rs;
                    }
                }
                $client->close();
            }
        }
    }

    /**
     * @param $uri
     * @return \suframe\core\components\net\tcp\Pool|bool
     */
    protected function getPool($path)
    {
        if (!$path) {
            return false;
        }
        foreach ($this->pools as $poolPath => $item) {
            if ($item && ($poolPath == '/' . $path)) {
                $key = array_rand($item);
                return $item[$key];
            }
        }
        return false;
    }

    /**
     * @return mixed
     */
    public function getPools() {
        return $this->pools;
    }

    /**
     * 动态增加连接池
     * @param $path
     * @param $config
     * @return bool
     */
    public function addPool($path, $config)
    {
        if (!isset($this->pools[$path])) {
            $this->pools[$path] = [];
        }

        foreach ($config as $item) {
            $key = md5($item['ip'] . ':' . $item['port']);
            if (!isset($this->pools[$path])) {
                $this->pools[$path] = [];
            }
            if (isset($this->pools[$path][$key]) && $this->pools[$path][$key]) {
                return false;
            }
            $this->pools[$path][$key] = new Pool($item['ip'], $item['port'], $item['size'] ?? 1,
                $item['overflowMax'] ?? null);
        }
    }

    /**
     * 动态删除连接池
     * @param $name
     */
    public function removePool($path, $host, $port){
        $key = md5($host . ':' . $port);
        if(isset($this->pools[$path]) && isset($this->pools[$path][$key])){
            while ($client = $this->pools[$path][$key]->get()){
                $client->close();
            }
            unset($this->pools[$path][$key]);
        }
    }

}