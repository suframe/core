<?php
/**
 * User: qian
 * Date: 2019/7/4 11:32
 */

namespace suframe\core\components\net\http;


class Out
{
    public static function success($response, $data = [], $message = '')
    {
        return static::write($response, $message, 200, $data);
    }

    /**
     * @param \Swoole\Http\Response $response
     * @param string $message
     * @param int $code
     * @return array
     */
    public static function notFound($response, $message = 'Not Found', $code = 404)
    {
        $response->status($code);
        return static::write($response, $message, $code);
    }

    /**
     * @param \Swoole\Http\Response $response
     * @param string $message
     * @param int $code
     * @return array
     */
    public static function error($response, $message = 'error', $code = 500)
    {
        $response->status($code);
        return static::write($response, $message, $code);
    }

    /**
     * @param \Swoole\Http\Response $response
     * @param string $message
     * @param string $code
     * @param array $data
     * @return array
     */
    public static function write($response, $message = '', $code = '', $data = [])
    {
        $rs = [
            'code' => $code,
            'data' => $data,
        ];
        if($message){
            $rs['message'] = $message;
        }
        $response->write(json_encode($rs));
        return $rs;
    }
}