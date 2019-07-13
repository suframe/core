<?php
return [
    'console' => require(__DIR__ . '/console.php'),
    'tcp' => require(__DIR__ . '/tcp.php'),
    'registerServer' => [
        'ip' => '127.0.0.1',
        'port' => 9500
    ],
];