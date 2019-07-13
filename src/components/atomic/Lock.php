<?php
/**
 * User: qian
 * Date: 2019/6/5 16:22
 */
namespace suframe\core\components\atomic;

use suframe\core\traits\Singleton;

class Lock
{
    use Singleton;

    protected $atomic;
    public function __construct() {
        $this->atomic = new \Swoole\Atomic(0);
    }

    /**
     * 加锁
     * @return bool
     */
    public function lock(){
        return $this->atomic->add(1) == 1;
    }

    /**
     * 释放锁
     */
    public function unlock(){
        $this->atomic->set(0);
    }

}