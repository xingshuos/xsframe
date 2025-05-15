<?php
return [
    // 演示测试
    'app\xs_kefu\service\AdminWebSocketWorker' => [
        'worker_num' => 4,
    ],
    // 聊天
    'app\xs_kefu\service\ChatWebSocketWorker'  => [
        'worker_num' => 4,
    ],
    'app\xs_kefu\service\ChatSSLWebSocketWorker'  => [
        'worker_num' => 4,
        // 'ssl_cert'   => '/path/to/cert.pem',
        // 'ssl_key'    => '/path/to/key.pem',
    ],
];