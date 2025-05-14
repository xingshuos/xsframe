<?php

namespace xsframe\console\command\make;

use think\console\Command;
use think\console\Input;
use think\console\Output;
use Workerman\Worker;

class WebSocket extends Command
{
    protected static $defaultName = 'websocket';

    protected function configure()
    {
        $this->setName('websocket:start')
            ->addArgument('app', null, 'app name')
            ->addArgument('service', null, 'service name')
            ->setDescription('启动指定应用的WebSocket服务');
    }

    protected function execute(Input $input, Output $output)
    {
        define('IA_ROOT', str_replace("\\", '/', dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))))));
        
        $app = $input->getArgument('app');
        $service = $input->getArgument('service');
        $className = "app\\{$app}\\service\\" . ucfirst($service) . 'WebSocketWorker';

        // 验证类是否存在
        if (!class_exists($className)) {
            $output->error("WebSocket Worker类不存在: {$className}");
            return;
        }

        $worker = new $className();

        // 启动服务
        Worker::runAll();
    }
}