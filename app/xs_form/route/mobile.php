<?php

// 目录地址: xs_form/route/mobile.php

use think\facade\Route;

Route::group('mobile', function () {
    Route::any('', 'mobile.index/index'); // 完整访问路径 http://www.xsframe.com/xs_form/mobile?i=1
    Route::any('index', 'mobile.index/index'); // 完整访问路径 http://www.xsframe.com/xs_form/mobile/index?i=1
    Route::any('test', 'mobile.index/test'); // 完整访问路径 http://www.xsframe.com/xs_form/mobile/test?i=1
})->middleware(\app\xs_form\middleware\StationOpenMiddleware::class);
