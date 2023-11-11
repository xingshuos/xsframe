<?php

use think\facade\Route;

Route::group(function () {

    //无需登录
    Route::group(function () {

    });

    // 需要登陆
    Route::group(function () {

    })->middleware(\app\xs_cloud\middleware\AuthLoginMiddleware::class);

})->middleware(\app\xs_cloud\middleware\StationOpenMiddleware::class);
