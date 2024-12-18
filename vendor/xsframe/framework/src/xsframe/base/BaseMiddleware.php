<?php

namespace xsframe\base;

use xsframe\interfaces\MiddlewareInterface;

class BaseMiddleware implements MiddlewareInterface
{
    protected $request = null;
    protected $params = [];

    /**
     * 检测中间件
     * @param $request
     * @param \Closure $next
     * @return mixed
     */
    public function handle($request, \Closure $next)
    {
        $this->request = $request;
        if (method_exists($this, '_initialize')) {
            $this->_initialize();
        }
        return $next($request);
    }

    // 初始化
    protected function _initialize()
    {
        $this->params = $this->request->param();
    }

    /**
     * 正确的数组数据
     * @param array $data
     * @param string $code
     * @param string $message
     * @return \think\response\Json
     */
    protected function success(array $data = [], string $code = "200", string $message = 'success'): \think\response\Json
    {
        $code = $data['code'] ?? $code;
        $message = $data['msg'] ?? $message;

        $retData = [
            'code' => (string)$code,
            'msg'  => $message,
            'data' => $data
        ];
        return json($retData);
    }

    /**
     * 错误的数组数据
     * @param string $message
     * @param string $code
     * @return array
     */
    protected function error(string $message = 'fail', string $code = "404"): array
    {
        $code = $data['code'] ?? $code;
        $message = $data['msg'] ?? $message;

        $retData = [
            'code' => (string)$code,
            'msg'  => $message,
            'data' => [],
        ];
        die(json_encode($retData));
    }
}