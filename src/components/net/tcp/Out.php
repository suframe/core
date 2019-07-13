<?php
/**
 * User: qian
 * Date: 2019/7/4 11:32
 */

namespace suframe\core\components\net\tcp;


class Out
{
    public static function success($server, $fd, $data = [], $message = '')
    {
        return static::write($server, $fd, $message, 200, $data);
    }

    /**
     * @param $server
     * @param $fd
     * @param string $message
     * @param int $code
     * @return array
     */
    public static function notFound($server, $fd, $message = 'Not Found', $code = 404)
    {
        return static::write($server, $fd, $message, $code);
    }

    /**
     * @param $server
     * @param $fd
     * @param string $message
     * @param int $code
     * @return array
     */
    public static function error($server, $fd, $message = 'error', $code = 500)
    {
        return static::write($server, $fd, $message, $code);
    }

    /**
     * @param \Swoole\Server $server
     * @param string $message
     * @param string $code
     * @param array $data
     * @return array
     */
    public static function write($server, $fd, $message = '', $code = '', $data = [])
    {
        $rs = [
            'code' => $code,
            'data' => $data,
        ];
        if($message){
            $rs['message'] = $message;
        }
        $server->send($fd, json_encode($rs));
        return $rs;
    }
}