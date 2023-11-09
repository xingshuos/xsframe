<?php

use think\facade\Route;


Route::group('', function () {

    //无需登录
    Route::group(function () {
        Route::any('', 'pc.index/index');
        Route::any('search', 'pc.index/search');
        Route::any('article/:id', 'pc.article/index');
        Route::any('artcate/:id', 'pc.article/category');

        Route::any('tags/:id', 'pc.goods/tags');
        Route::any('view/:id', 'pc.goods/view');
        Route::any('goods/:id', 'pc.goods/index')->pattern(['id' => '\d+']); // 限定路由必须是数字
        Route::any('gcate/:id', 'pc.goods/category');
        Route::any('product/:id', 'pc.goods/index');
        Route::any('goods/detail', 'pc.goods/detail');
        Route::any('goods/chapters', 'pc.goods/chapters');
        Route::any('goods/getInfo', 'pc.goods/getGoodsInfo');

        Route::any('user/login', 'pc.login/index');
        Route::any('user/register', 'pc.login/register');
        Route::any('user/forget', 'pc.login/forget');
        Route::any('user/logout', 'pc.login/logout');

        Route::any('sms/login', 'pc.sms/login');
        Route::any('sms/register', 'pc.sms/register');
        Route::any('sms/forget', 'pc.sms/forget');
        Route::any('sms/check', 'pc.sms/check');

        Route::any('artist/list', 'pc.teacher/index');
        Route::any('artist/detail', 'pc.teacher/detail');
        Route::any('artist/getList', 'pc.teacher/getTeacherList');

        // 接口返回JSON
        Route::any('goods/getGoodsList', 'pc.goods/getGoodsList');
    });

    // 需要登陆
    Route::group(function () {
        Route::any('user/center', 'pc.user/index');
        Route::any('user/info', 'pc.user/info');
        Route::any('user/address', 'pc.user/address');
        Route::any('user/coupon', 'pc.user/coupon');
        Route::any('user/coupon_get', 'pc.user/couponGet');
        Route::any('user/course', 'pc.user/course');
        Route::any('user/history', 'pc.user/history');
        Route::any('user/favorite', 'pc.user/favorite');
        Route::any('user/order', 'pc.user/order');
        Route::any('user/order_detail', 'pc.user/orderDetail');
        Route::any('user/balance', 'pc.user/balance');
        Route::any('user/update', 'pc.user/update');

        Route::any('order/list', 'pc.order/index');
        Route::any('order/detail', 'pc.order/detail');
        Route::any('order/confirm', 'pc.order/confirm');
        Route::any('order/create', 'pc.order/create');
        Route::any('order/payment', 'pc.order/payment');
        Route::any('order/wechat', 'pc.order/wechat');
        Route::any('order/alipay', 'pc.order/alipay');
        Route::any('order/credit', 'pc.order/credit');
        Route::any('order/check', 'pc.order/check');
        Route::any('order/cancel', 'pc.order/cancel');
        Route::any('order/data', 'pc.order/data');

        Route::any('address/list', 'pc.userAddress/index');
        Route::any('address/detail', 'pc.userAddress/detail');
        Route::any('address/delete', 'pc.userAddress/delete');
        Route::any('address/edit', 'pc.userAddress/edit');
        Route::any('address/add', 'pc.userAddress/add');
        Route::any('address/area', 'pc.userAddress/area');

        Route::any('cart/list', 'pc.cart/index');
        Route::any('cart/edit', 'pc.cart/edit');
        Route::any('cart/delete', 'pc.cart/delete');
        Route::any('cart/add', 'pc.cart/add');

        Route::any('favorite/list', 'pc.userFavorite/index');
        Route::any('favorite/add', 'pc.userFavorite/add');
        Route::any('favorite/delete', 'pc.userFavorite/delete');

        Route::any('teacher/list', 'pc.teacher/index');
        Route::any('teacher/detail', 'pc.teacher/detail');

        Route::any('course/list', 'pc.userCourse/index');
        Route::any('course/history', 'pc.userCourse/history');
        Route::any('course/play', 'pc.userCourse/play');
        Route::any('course/getPlayInfo', 'pc.userCourse/getPlayInfo');
        Route::any('course/getPlayAuth', 'pc.userCourse/getPlayAuth');

        Route::any('record/list', 'pc.userRecord/index');
    })->middleware(\app\store\middleware\AuthLoginMiddleware::class);

})->middleware(\app\store\middleware\StationOpenMiddleware::class);
