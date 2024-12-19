<?php

use think\facade\Route;

Route::group('api', function () {

    // 无需登录
    Route::group(function () {
        Route::any('home/index', 'api.index/index'); // 完整访问路径 http://www.xsframe.com/xs_form/api/home/index.html?i=1
    })->allowCrossDomain();

    // 需要登陆
    Route::group(function () {

    })->middleware(\app\xs_form\middleware\AuthLoginMiddleware::class);

})->middleware(\app\xs_form\middleware\StationOpenMiddleware::class);
