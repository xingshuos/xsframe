<?php

namespace xsframe\console\command\make;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use Workerman\Worker;

class WebSocket extends Command
{
    protected static $defaultName = 'xs:worker';

    protected function configure()
    {
        $this->setName(self::$defaultName)
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload|status|connections", 'start')
            ->addArgument('app', null, 'app name')
            ->addArgument('service', null, 'service name')
            ->addOption('host', 'H', Option::VALUE_OPTIONAL, 'the host of workerman server.', null)
            ->addOption('port', 'p', Option::VALUE_OPTIONAL, 'the port of workerman server.', null)
            ->addOption('daemon', 'd', Option::VALUE_NONE, 'Run the workerman server in daemon mode.')
            ->setDescription('启动指定应用的WebSocket服务');
    }

    protected function execute(Input $input, Output $output)
    {
        define('IA_ROOT', str_replace("\\", '/', dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))))));

        $action = $input->getArgument('action');
        $app = $input->getArgument('app');
        $service = $input->getArgument('service');

        if (!in_array($action, ['start', 'stop', 'reload', 'restart', 'status', 'connections'])) {
            $output->writeln("Invalid argument action:{$action}, Expected start|stop|restart|reload|status|connections .");
            exit(1);
        }

        if (DIRECTORY_SEPARATOR !== '\\') { // linux系统
            global $argv;
            array_shift($argv);
            array_shift($argv);
            array_unshift($argv, 'think', $action);
        } else {// window 系统
            // $output->writeln("GatewayWorker Not Support On Windows.");
            // exit(1);
        }

        if ('start' == $action) {
            $output->writeln('Starting XsWorker server...');
        }

        #  需要配活操作（在config/websocket.php中配置多个服务） TODO linux支持多进程执行，windows只支持单进程执行
        $className = "app\\{$app}\\service\\" . ucfirst($service) . 'WebSocketWorker';
        // $classSSLName = "app\\{$app}\\service\\" . ucfirst($service) . 'SSLWebSocketWorker';

        // 验证类是否存在
        if (!class_exists($className)) {
            $output->error("WebSocket Worker类不存在: {$className}");
            return;
        }

        // 开启守护进程模式
        if ($input->hasOption('daemon')) {
            Worker::$daemonize = true;
        }

        $worker = new $className();
        // $workerSSL = new $classSSLName();

        // 启动服务
        Worker::runAll();
    }
}