<?php
/**
 * User: qian
 * Date: 2019/7/8 17:04
 */

namespace suframe\core\components\rpc;


class RpcUnPack
{

    protected $data = [];

    public function __construct($data)
    {
        if (is_string($data)) {
            $this->data = json_decode($data, true);
        } else {
            $this->data = $data;
        }
    }

    public function unpack(): array
    {
        return $this->data;
    }

    public function get($key = null, $def = null)
    {
        if($key === null){
            return $this->data;
        }
        return $this->data[$key] ?? $def;
    }

}