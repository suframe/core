<?php
/**
 * User: qian
 * Date: 2019/6/10 10:12
 */

namespace suframe\core\components\net\http;

use Swoole\Client;
use Swoole\Coroutine\Channel;

/**
 * tcp连接池
 * Class TcpPool
 * @package suframe\proxy\components
 */
class Pool {

	protected $maxReTry = 200;
	protected $timeout = 0.1;
	protected $overflow = 0; //溢出连接
	protected $overflowMax; //最大溢出连接

	/**
	 * @var Channel
	 */
	protected $pool;
	protected $host;
	protected $port;
	protected $size;

	public function __construct($host, $port, $size = 1, $overflowMax = null) {
	    $this->host = $host;
	    $this->port = $port;
		$this->size = $size;
		$this->overflowMax = $overflowMax ?: $size * 4;
        $this->createPool();
	}

	public function createPool() {
		$size = $this->size;
        $this->pool = new Channel($size);
	}

    /**
     * @param Client $client
     * @return bool
     */
	public function put($client) {
	    if($this->pool->length() > $this->size){
            $client->close();
            $this->overflow--;
            return false;
        }
		$this->pool->push($client);
	}

	public function getLength() {
		return $this->pool->length();
	}

	/**
	 * @return Client|null
	 */
	public function get() {
//        echo "连接池有连接:{$this->pool->length()}\n";
        if($this->pool->length()){
            return $this->pool->pop($this->timeout);
        }
        if($this->overflow > $this->overflowMax){
            //超过最大长连接支持
            return null;
        }

        $client = new Client(SWOOLE_SOCK_TCP | SWOOLE_KEEP);
        $res = @$client->connect($this->host, $this->port);
        if ($res) {
            $this->overflow++;
//            echo "获取新连接,当前总数{$this->overflow}, {$this->pool->length()} \n";
            return $client;
        }
	}

    /**
     * @return int
     */
    public function getOverflow(): int
    {
        return $this->overflow;
    }

}