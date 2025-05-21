<?php

namespace xsframe\console\command\make;

use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Config;
use Workerman\Worker;

class WebSocket extends Command
{
    protected static $defaultName = 'xs:worker';

    // php think xs:worker start  启动全部
    // php think xs:worker start --app=xs_kefu --service=chat 指定启动
    // php think xs:worker start -a xs_kefu -s chat 别名启动
    protected function configure()
    {
        $this->setName(self::$defaultName)
            ->addArgument('action', Argument::OPTIONAL, "start|stop|restart|reload|status|connections", 'start')
            ->addOption('app', 'a', Option::VALUE_OPTIONAL, 'the app of workerman server.', null)
            ->addOption('service', 's', Option::VALUE_OPTIONAL, 'the service of workerman server.', null)
            ->addOption('daemon', 'd', Option::VALUE_NONE, 'Run the workerman server in daemon mode.')
            ->setDescription('启动指定应用的WebSocket服务');
    }

    protected function execute(Input $input, Output $output)
    {
        define('IA_ROOT', str_replace("\\", '/', dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(dirname(__FILE__)))))))))));

        $action = $input->getArgument('action');
        $app = $input->getOption('app');
        $service = $input->getOption('service');

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
            if (!empty($app) && !empty($service)) {

            } else {
                $output->writeln("windows系统只支持单进程运行，默认运行配置文件中第一个服务");
            }
            // exit(1);
        }

        if ('start' == $action) {
            $output->writeln('Starting XsWorker server...');
        }

        // 开启守护进程模式
        if ($input->hasOption('daemon')) {
            Worker::$daemonize = true;
        }

        #  需要配活操作（在config/websocket.php中配置多个服务） TODO linux支持多进程执行，windows只支持单进程执行
        if (!empty($app) && !empty($service)) {
            $className = "app\\{$app}\\service\\" . ucfirst($service) . 'WebSocketWorker';
            new $className();
        } else {
            $websocketConfigList = Config::get('websocket');
            foreach ($websocketConfigList as $className => $value) {
                $filePath = str_replace('\\', '/', IA_ROOT . "/" . $className . ".php");

                if (is_file($filePath)) {

                    // 验证类是否存在
                    if (!class_exists($className)) {
                        $output->error("WebSocket Worker类不存在: {$className}");
                        return;
                    }

                    new $className();
                }
            }
        }

        // 启动服务
        Worker::runAll();
    }
}