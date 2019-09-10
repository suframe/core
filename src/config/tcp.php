<?php
return [
    'server' => [
        'listen' => '0.0.0.0',
        'port' => 9501,
        'options' => [
            //https://wiki.swoole.com/wiki/page/274.html 配置详见
            'log_file' => SUMMER_APP_ROOT . 'runtime/swoole.log',
            'pid_file' => SUMMER_APP_ROOT . 'runtime/swoole.pid'
        ]
    ],
    'dispatchType' => 'tcp'
];