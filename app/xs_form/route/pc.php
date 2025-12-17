<?php

// 目录地址: xs_form/route/pc.php

use think\facade\Route;


Route::group('pc', function () {

    //无需登录
    Route::group(function () {
        Route::any('', 'pc.index/index');
    });

    // 需要登陆
    Route::group(function () {

    })->middleware(\app\xs_form\middleware\AuthLoginMiddleware::class);

})->middleware(\app\xs_form\middleware\StationOpenMiddleware::class);
