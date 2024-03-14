<?php

// +----------------------------------------------------------------------
// | 星数 [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2023~2024 http://xsframe.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: guiHai <786824455@qq.com>
// +----------------------------------------------------------------------

namespace xsframe\exception;

use xsframe\enum\ExceptionEnum;
use think\exception\Handle;
use think\exception\HttpException;
use think\Response;
use Throwable;
use think\exception\ValidateException;
use xsframe\util\LoggerUtil;

class ExceptionHandler extends Handle
{
    private $code;
    private $msg;

    public function render($request, Throwable $e): Response
    {
        // 数据验证异常
        if ($e instanceof ValidateException) {
            $code = intval(ExceptionEnum::API_PARAMS_ERROR_CODE);
            $msg = $e->getError();
            $result = [
                'code' => $code,
                'msg'  => $msg,
            ];
            return json($result, $code);
        }

        // HTTP异常
        if ($e instanceof HttpException && $request->isAjax()) {
            $code = intval($e->getStatusCode());
            $msg = $e->getMessage();

            $result = [
                'code' => $code,
                'msg'  => $msg,
            ];
            return json($result, $code);
        }

        // BaseException请求异常POST
        if ($e instanceof BaseException && ($request->isPost() || $request->isGet())) {
            $code = intval(intval($e->code));
            $msg = $e->msg;

            $result = [];
            $result['code'] = $code;
            $result['msg'] = $msg;

            if (env('APP_DEBUG')) {
                $result['error'] = [
                    'file'  => $e->getFile(),
                    'line'  => $e->getLine(),
                    'trace' => $e->getTrace(),
                ];

                // 记录异常信息到日志
                $logData = [
                    'url'       => $request->url(),
                    'method'    => $request->method(),
                    'ip'        => $request->ip(),
                    'params'    => $request->param(),
                    'exception' => $e->getMessage(),
                    'code'      => $e->getCode(),
                    'file'      => $e->getFile(),
                    'line'      => $e->getLine(),
                ];
                LoggerUtil::error($logData);
            }
            return json($result, $code);
        }

        // 其他错误交给系统处理
        return parent::render($request, $e);
    }
}