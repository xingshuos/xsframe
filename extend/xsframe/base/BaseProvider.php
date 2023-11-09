<?php

return [
    // 绑定自定义异常处理handle类
    'think\exception\Handle' => \xsframe\exception\ExceptionHandler::class,
];