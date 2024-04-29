<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK IT ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006-2018 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------
// +----------------------------------------------------------------------
// | Workerman设置 仅对 php think worker:gateway 指令有效
// +----------------------------------------------------------------------
return [
    // 扩展自身需要的配置
    'protocol'              => 'websocket', // 协议 支持 tcp udp unix http websocket text
    'host'                  => '0.0.0.0', // 监听地址
    'port'                  => 2348, // 监听端口
    'socket'                => '', // 完整监听地址
    'context'               => [], // socket 上下文选项
    'register_deploy'       => true, // 是否需要部署register
    'businessWorker_deploy' => true, // 是否需要部署businessWorker
    'gateway_deploy'        => true, // 是否需要部署gateway

    // Register配置
    'registerAddress'       => '127.0.0.1:1236', // 注册服务地址

    // Gateway配置
    'name'                  => 'xsframe:gateway_worker', // 可以设置Gateway进程的名称，方便status命令中查看统计
    'count'                 => 1, // Gateway进程的数量,以便充分利用多cpu资源
    'lanIp'                 => '127.0.0.1', // lanIp是Gateway所在服务器的内网IP，如果不做多服务器分布式部署的话默认填写127.0.0.1即可
    'startPort'             => 2000, //  Gateway进程启动后会监听一个本机端口，用来给BusinessWorker提供链接服务，然后Gateway与BusinessWorker之间就通过这个连接通讯。这里设置的是Gateway监听本机端口的起始端口。比如启动了4个Gateway进程，startPort为2000，则每个Gateway进程分别启动的本地端口一般为2000、2001、2002、2003。
    'daemonize'             => false,// 是否以守护进程模式启动（linux下有效 ）
    'pingInterval'          => 30, // 心跳间隔时间
    'pingNotResponseLimit'  => 0, // 服务端是否允许客户端不发送心跳 如果配置为0则服务端允许客户端不发心跳 ，如果配置为1 客户端必须要在心跳间隔时间（pingInterval）内发送心跳到服务器否则服务器会判定客户端断开而终端连接触发onclose事件。
    'pingData'              => '{"type":"ping"}', //  如果pingData 不是空字符串则服务器会在心跳间隔时间（pingInterval）向客户端发送心跳数据

    // BusinsessWorker配置
    'businessWorker'        => [
        'name'         => 'BusinessWorker',
        'count'        => 1,
        'eventHandler' => '\think\worker\Events', // 事件处理类
    ],

];
