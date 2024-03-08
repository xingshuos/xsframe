<?php

use think\facade\Route;

Route::group('api', function () {

    //无需登录
    Route::group(function () {
        Route::any('upgrade', 'api.upgrade/getUpgradeList');
        Route::any('upgradeFiles', 'api.upgrade/getUpgradeFiles');
        Route::any('upgradeFileData', 'api.upgrade/getUpgradeFileData');
        Route::any('checkVersion', 'api.upgrade/checkUpgradeVersion');
    });

    // 需要登陆
    Route::group(function () {

    })->middleware(\app\xs_cloud\middleware\AuthLoginMiddleware::class);

})->middleware(\app\xs_cloud\middleware\StationOpenMiddleware::class);
