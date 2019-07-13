<?php
/**
 * User: qian
 * Date: 2019/7/10 16:20
 */

namespace suframe\core\components\rpc;

class SRpc implements SRpcInterface
{

    /**
     * @param $path
     */
    static public function route($path)
    {
       return Client::getInstance($path);
        // TODO: Implement route() method.
    }
}