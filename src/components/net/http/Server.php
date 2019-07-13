<?php
namespace suframe\core\components\net\http;

class Server {
    /**
     * @var \Swoole\Http\Server
     */
    protected $server;

    protected $registerPort;

    /**
     * @return mixed
     */
    public function getRegisterPort()
    {
        return $this->registerPort;
    }

    /**
     * @param array $config
     * @return \Swoole\Http\Server
     * @throws \Exception
     */
    public function create(array $config) {
        $this->server = $server = new \Swoole\Http\Server($config['server']['listen'], $config['server']['port']);
        $this->set($config['swoole']);
        $register = $config['register'] ?? null;
        if($register){
            $this->registerPort = $register['port'];
            $this->server->listen($register['listen'], $register['port'], SWOOLE_SOCK_TCP);
        }
        return $this->server;
    }

    /**
     * @throws \Exception
     */
    public function set(array $setting) {
        $this->getServer()->set($setting);
    }

    /**
     * @throws \Exception
     */
    public function start() {
        $this->getServer()->start();
    }

    /**
     * @return \Swoole\Server
     * @throws \Exception
     */
    public function getServer() {
        if (!$this->server) {
            throw new \Exception('please create server');
        }
        return $this->server;
    }

    /**
     * @param mixed $server
     */
    public function setServer($server) {
        $this->server = $server;
    }
}