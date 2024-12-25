<?php
// +----------------------------------------------------------------------
// | ThinkPHP [ WE CAN DO IT JUST THINK ]
// +----------------------------------------------------------------------
// | Copyright (c) 2006~2021 http://thinkphp.cn All rights reserved.
// +----------------------------------------------------------------------
// | Licensed ( http://www.apache.org/licenses/LICENSE-2.0 )
// +----------------------------------------------------------------------
// | Author: liu21st <liu21st@gmail.com>
// +----------------------------------------------------------------------

namespace xsframe\console\command\make;

use xsframe\console\command\Make;
use think\console\input\Option;
use think\console\Input;
use think\console\Output;

class Controller extends Make
{
    protected $type = "Controller";
    protected $tableName = "";

    // 操作说明

    // 创建api控制器
    // 常规:php think make:controller xs_test@api/member --api --table=xs_test
    // 缩写:php think make:controller xs_test@api/member --api -t xs_test

    // 创建mobile控制器 -v index 同时生成对应的view文件
    // 常规:php think make:controller xs_test@mobile/index --mobile -v index

    // 创建web控制器 -v 同时生成对应的view文件
    // 常规:php think make:controller xs_test@web/member --web --table=xs_test --view=member/index
    // 缩写:php think make:controller xs_test@web/member --web -t xs_test -v member/index

    protected function configure()
    {
        parent::configure();
        $this->setName("make:controller")
            ->addOption('plain', null, Option::VALUE_NONE, 'Generate an empty controller class.')
            ->addOption('api', null, Option::VALUE_NONE, 'Generate an api controller class.')
            ->addOption('mobile', null, Option::VALUE_NONE, 'Generate an mobile controller class.')
            ->addOption('web', null, Option::VALUE_NONE, 'Generate an web controller class.')
            ->addOption('table', 't', Option::VALUE_REQUIRED, 'Set table name', "")
            ->setDescription('Create a new resource controller class 2');
    }

    /**
     * 命令行执行入口
     * @param Input $input
     * @param Output $output
     * @return false|void
     */
    protected function execute(Input $input, Output $output)
    {
        $name = trim($input->getArgument('name'));

        $classname = $this->getClassName($name);

        $pathname = $this->getPathName($classname);

        $this->tableName = $input->getOption('table');

        if (is_file($pathname)) {
            $output->writeln('<error>' . $this->type . ':' . $classname . ' already exists!</error>');
            return false;
        }

        if (!is_dir(dirname($pathname))) {
            mkdir(dirname($pathname), 0755, true);
        }

        $pathname = $this->capitalizeLastFilename($pathname);
        file_put_contents($pathname, $this->buildClass($classname));

        $output->writeln('<info>' . $this->type . ':' . $classname . ' created successfully.</info>');
    }

    protected function buildClass(string $name)
    {
        $stub = file_get_contents($this->getStub());

        $namespace = trim(implode('\\', array_slice(explode('\\', $name), 0, -1)), '\\');

        $class = str_replace($namespace . '\\', '', $name);

        return str_replace(['{%className%}', '{%actionSuffix%}', '{%namespace%}', '{%app_namespace%}', '{%tableName%}'], [
            ucfirst($class),
            $this->app->config->get('route.action_suffix'),
            $namespace,
            $this->app->getNamespace(),
            $this->tableName,
        ], $stub);
    }

    protected function getStub(): string
    {
        $stubPath = __DIR__ . DIRECTORY_SEPARATOR . 'stubs' . DIRECTORY_SEPARATOR;

        if ($this->input->getOption('plain')) {
            return $stubPath . 'controller.plain.stub';
        }

        if ($this->input->getOption('api')) {
            return $stubPath . 'controller.api.stub';
        }

        if ($this->input->getOption('mobile')) {
            return $stubPath . 'controller.mobile.stub';
        }

        if ($this->input->getOption('web')) {
            return $stubPath . 'controller.web.stub';
        }

        return $stubPath . 'controller.stub';
    }

    protected function getClassName(string $name): string
    {
        return parent::getClassName($name) . ($this->app->config->get('route.controller_suffix') ? 'Controller' : '');
    }

    protected function getNamespace(string $app): string
    {
        return parent::getNamespace($app) . '\\controller';
    }

}
