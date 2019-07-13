<?php

namespace suframe\core\components\rpc;

class RpcPack
{
    protected $data = [];
    public function __construct($path)
    {
        $this->data['path'] = $path;
    }

    public function add($key, $value)
    {
        $this->data[$key] = $value;
        return $this;
    }

    public function pack(){
        return json_encode($this->data);
    }

}