<?php

use think\facade\Route;

Route::group('api', function () {

    //无需登录
    Route::group(function () {
        Route::any('frame/upgrade', 'api.frames/getUpgradeList');
        Route::any('frame/upgradeFiles', 'api.frames/getUpgradeFiles');
        Route::any('frame/upgradeFileData', 'api.frames/getUpgradeFileData');
        Route::any('frame/checkVersion', 'api.frames/checkUpgradeVersion');

        Route::any('app/list', 'api.apps/getAppList');
        Route::any('app/upgrade', 'api.apps/checkAppUpgrade');
        Route::any('app/download', 'api.apps/downloadModule');
    });

    // 需要登陆
    Route::group(function () {

    })->middleware(\app\xs_cloud\middleware\AuthLoginMiddleware::class);

})->middleware(\app\xs_cloud\middleware\StationOpenMiddleware::class);
