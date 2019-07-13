<?php
/**
 * User: qian
 * Date: 2019/7/1 16:36
 */

namespace suframe\core\components\register;

use suframe\core\components\Config;
use suframe\core\components\register\Client as ClientAlias;
use suframe\core\components\swoole\ProcessTools;
use suframe\core\traits\Singleton;
use Swoole\Client;
use Swoole\Timer as SwooleTimer;

/**
 * 服务定时同步
 * Class SyncServers
 * @package suframe\register\components
 */
class Timer
{
    use Singleton;

    protected $timer = [];

    /**
     * @return Config
     */
    public function getConfig(): Config
    {
        return ClientAlias::getInstance()->reloadServer();
    }

    /**
     * 创建定时器
     * @return bool
     */
    public function createTimer()
    {
        if ($this->timer) {
            return false;
        }
        $timerMs = $this->getConfig()->get('tcp.timerMs', 1000 * 60);
        $timer = SwooleTimer::tick($timerMs, function (){
            static::getInstance()->check();
        });
        $this->timer = [
            'created_time' => date('Y-m-d H:i:s'),
            'timer' => $timer
        ];
        return true;
    }

    /**
     * 检测定时器
     * @return array
     */
    public function checkTimer()
    {
        return $this->timer;
    }

    /**
     * 清除定时器
     * @return bool
     */
    public function clearTimer()
    {
        if ($this->timer) {
            SwooleTimer::clear($this->timer['timer']);
            $this->timer = [];
            return true;
        }
        return false;
    }

    /**
     * 检测服务
     * @return bool
     */
    public function check()
    {
        $servers = $this->getConfig()->get('servers');
        $hasChange = false;
        //检测
        foreach ($servers as $path => $server) {
            /** @var Config $item */
            foreach ($server as $key => $item) {
                $client = new Client(SWOOLE_SOCK_TCP);
//                echo "check: {$item['ip']}:{$item['port']}\n";
                if (!@$client->connect($item['ip'], $item['port'], -1)) {
                    $hasChange = true;
                    //剔除
                    echo "{$item['ip']}:{$item['port']}: has error\n";
                    unset($server[$key]);
                    continue;
                }
                $client->close();
            }
        }
        if ($hasChange) {
            $config = Config::getInstance();
            $servers = $config->get('servers');
            ClientAlias::getInstance()->updateLocalFile($servers->toArray());
            Server::getInstance()->notify(ClientAlias::COMMAND_UPDATE_SERVERS);
            //重启服务
            ProcessTools::kill();
            echo "notify \n";
        }
        return true;
    }

}