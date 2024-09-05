<?php


namespace xsframe\base;

use think\worker\Server;

class WorkerBaseService extends Server
{
    // php think worker:server
    // https://www.workerman.net/doc/gateway-worker
    // https://gitee.com/lyzaidxh/php_code_snippet/blob/master/thinkphp6%E9%9B%86%E6%88%90workerman%E5%92%8CGatewayWorker

    protected $socket = 'websocket://0.0.0.0:2345';

    /*
    @method 		发送消息
    @param          data              数据
    */
    protected function onMessage($connection, $data)
    {
        /**对接收消息做json处理*/
        // $rest = json_decode($data);
        // id = $connection->id
        // ip = $connection->getRemoteIp();
        $connection->send('receive success' . $connection->id . PHP_EOL);

        print_r('接收到消息:' . $data . PHP_EOL);
    }

    /**
     * 当连接建立时触发的回调函数
     * @param $connection
     */
    protected function onConnect($connection)
    {
        print_r('连接成功' . PHP_EOL);
    }


    /**
     * 当连接断开时触发的回调函数
     * @param $connection
     */
    protected function onClose($connection)
    {
        print_r('用户断开了链接！' . PHP_EOL);
    }


    /**
     * 当客户端的连接上发生错误时触发
     * @param $connection
     * @param $code
     * @param $msg
     */
    protected function onError($connection, $code, $msg)
    {
        echo "error $code $msg\n";
    }

    /**
     * 每个进程启动
     * @param $worker
     */
    protected function onWorkerStart($worker)
    {
        print_r('启动成功' . PHP_EOL);
    }
}