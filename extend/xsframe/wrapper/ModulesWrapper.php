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

namespace xsframe\wrapper;

use DOMDocument;
use xsframe\util\FileUtil;
use xsframe\util\PinYinUtil;
use think\facade\Db;
use xsframe\util\RequestUtil;

class ModulesWrapper
{
    // 执行安装应用
    public function runInstalledModule($moduleName, $key = null, $token = null)
    {
        if (is_dir(IA_ROOT . '/app/' . $moduleName)) {
            $manifest = $this->extModuleManifest($moduleName);
            $this->extModuleRunScript($manifest, 'install');
        } else {
            $cloudWrapper = new CloudWrapper();
            $ret = $cloudWrapper->downloadCloudApp($moduleName, $key, $token);
            if ($ret) {
                $this->runInstalledModule($moduleName, $key, $token);
            }
        }
        return true;
    }

    // 执行卸载应用
    public function runUninstalledModule($moduleName)
    {
        $manifest = $this->extModuleManifest($moduleName);
        $this->extModuleRunScript($manifest, 'uninstall');
        return true;
    }

    // 执行升级应用
    public function runUpgradeModule($moduleName, $key = null, $token = null, $isCloud = false)
    {
        $ret = true;
        if ($isCloud) {
            $cloudWrapper = new CloudWrapper();
            $ret = $cloudWrapper->downloadCloudApp($moduleName, $key, $token);
        }

        if ($ret) {
            $manifest = $this->extModuleManifest($moduleName);
            $this->extModuleRunScript($manifest, 'upgrade');
            return $manifest;
        }
        return false;
    }

    // 移动客户端文件
    public function moveDirToPublic($moduleName)
    {
        $modulePath = IA_ROOT . '/app/' . $moduleName;
        $modulePackagesPath = $modulePath . '/packages/';
        $moduleDirList = glob($modulePackagesPath . '*');
        if (empty($moduleDirList)) {
            return true;
        }

        $targetDirPath = IA_ROOT . '/public/app/' . $moduleName . '/';
        if (!is_dir($targetDirPath)) {
            FileUtil::mkDirs($targetDirPath);
        }

        if (is_file($modulePath . "/icon.png") && !is_file($targetDirPath . "/icon.png")) {
            @copy($modulePath . "/icon.png", $targetDirPath . "/icon.png");
        }

        FileUtil::oldDirToNewDir($modulePackagesPath, $targetDirPath);
        return true;
    }

    // 创建未安装应用
    public function buildLocalUnInstalledModule(): bool
    {
        $moduleList = Db::name('sys_modules')->column('*', 'identifie');
        // dump($moduleList);

        $module_root = IA_ROOT . '/app';
        $module_path_list = glob($module_root . '/*');
        if (empty($module_path_list)) {
            return true;
        }
        // dump($module_path_list);

        foreach ($module_path_list as $path) {
            $moduleName = pathinfo($path, PATHINFO_BASENAME);

            if (!empty($moduleList[$moduleName]) || in_array($moduleName, ['admin', 'provider.php'])) {
                continue;
            }

            if (!file_exists($path . '/packages/manifest.xml')) {
                continue;
            }

            $manifest = $this->extModuleManifest($moduleName);

            $moduleUpgradeData = [
                'type'         => $manifest['application']['type'],
                'name'         => $manifest['application']['name'],
                'identifie'    => $manifest['application']['identifie'],
                'version'      => $manifest['application']['version'],
                'author'       => $manifest['application']['author'],
                'logo'         => $manifest['application']['logo'],
                'ability'      => $manifest['application']['ability'],
                'description'  => $manifest['application']['description'],
                'create_time'  => time(),
                'update_time'  => time(),
                'name_initial' => PinYinUtil::getFirstPinyin($manifest['application']['name']),
            ];

            if (!empty($manifest['platform']['supports'])) {
                foreach (['wechat', 'wxapp', 'pc', 'app', 'h5', 'aliapp', 'bdapp', 'uniapp'] as $support) {
                    if (in_array($support, $manifest['platform']['supports'])) {
                        $moduleUpgradeData["{$support}_support"] = 1;
                    }
                }
            }

            if (empty($moduleUpgradeData['name'])) {
                continue;
            }
            // 验证应用名称是否正确
            if (empty($moduleUpgradeData['identifie']) || !preg_match('/^[a-z][a-z\d_]+$/i', $moduleUpgradeData['identifie'])) {
                continue;
            }
            // 验证应用目录名称与配置名称是否匹配
            if (strtolower($moduleName) != strtolower($moduleUpgradeData['identifie'])) {
                continue;
            }
            // 验证version格式是否正确
            if (empty($moduleUpgradeData['version']) || !preg_match('/^[\d\.]+$/i', $manifest['application']['version'])) {
                continue;
            }

            Db::name('sys_modules')->insert($moduleUpgradeData);
        }

        return true;
    }

    // 获取云更新应用
    public function buildCloudUnInstalledModule($key = null, $token = null)
    {
        if ($key && $token) {
            $moduleList = Db::name('sys_modules')->column('*', 'identifie');

            $result = RequestUtil::cloudHttpPost("app/list", ['key' => $key, 'token' => $token]);
            
            if (!empty($result) && intval($result['code']) == 200) {
                $appList = $result['data']['appList'];

                foreach ($appList as $appInfo) {
                    $moduleName = $appInfo['identifier'];
                    if (!empty($moduleList[$moduleName])) {
                        continue;
                    }

                    $moduleUpgradeData = [
                        'type'         => $appInfo['type'],
                        'name'         => $appInfo['name'],
                        'identifie'    => $appInfo['identifier'],
                        'version'      => $appInfo['version'],
                        'author'       => $appInfo['author'],
                        'logo'         => $appInfo['logo'],
                        'ability'      => $appInfo['ability'],
                        'description'  => $appInfo['description'],
                        'create_time'  => time(),
                        'update_time'  => time(),
                        'name_initial' => PinYinUtil::getFirstPinyin($appInfo['name']),
                        'is_cloud'     => 1,
                    ];

                    if (empty($moduleUpgradeData['name'])) {
                        continue;
                    }
                    // 验证应用名称是否正确
                    if (empty($moduleUpgradeData['identifie']) || !preg_match('/^[a-z][a-z\d_]+$/i', $moduleUpgradeData['identifie'])) {
                        continue;
                    }
                    // 验证version格式是否正确
                    if (empty($moduleUpgradeData['version']) || !preg_match('/^[\d\.]+$/i', $moduleUpgradeData['version'])) {
                        continue;
                    }

                    Db::name('sys_modules')->insert($moduleUpgradeData);
                }
            }

        }

        return true;
    }

    public function extModuleManifest($moduleName)
    {
        $root = IA_ROOT . '/app/' . $moduleName . "/packages";
        $filename = $root . '/manifest.xml';
        if (!file_exists($filename)) {
            return [];
        }

        $xml = file_get_contents($filename);
        $xml = $this->extModuleManifestParse($xml);

        if (!empty($xml)) {
            $xml['application']['logo'] = "app/" . $moduleName . '/icon.png';

            $appLogoPath = $root . '/icon.png';

            if (is_file($appLogoPath)) {
                $publicAppPath = IA_ROOT . "/public/app/{$moduleName}";
                $publicAppLogoPath = IA_ROOT . "/public/app/{$moduleName}" . '/icon.png';

                if (!is_dir($publicAppPath)) {
                    FileUtil::mkDirs($publicAppPath);
                }

                @copy($appLogoPath, $publicAppLogoPath);
            }

            if (empty($xml['platform']['supports'])) {
                $xml['platform']['supports'][] = 'app';
            }
        }
        return $xml;
    }

    // 安装应用
    private function extModuleRunScript($manifest, $scriptType)
    {
        if (!in_array($scriptType, ['install', 'uninstall', 'upgrade'])) {
            return false;
        }
        $moduleName = $manifest['application']['identifie'];
        $modulePath = IA_ROOT . '/app/' . $moduleName . "/packages/";

        if (!empty($manifest[$scriptType])) {
            if (strexists($manifest[$scriptType], '.php')) {
                if (file_exists($modulePath . $manifest[$scriptType])) {
                    $sql = include_once $modulePath . $manifest[$scriptType];
                    $installSqlArray = $this->sqlParse($sql);
                    foreach ($installSqlArray as $sql) {
                        try {
                            $sql = trim($sql);
                            if (!empty($sql)) {
                                Db::execute($sql);
                            }
                        } catch (\Exception $e) {
                        }
                    }
                }
            } else {
                try {
                    $sql = trim($manifest[$scriptType]);
                    if (!empty($sql)) {
                        Db::execute($sql);
                    }
                } catch (\Exception $e) {
                }
            }
        }

        # 是否线上应用 如果是清空安装文件 TODO
        $isOnlineModule = false;
        if ($isOnlineModule) {
            $this->extModuleScriptClean($moduleName, $manifest);
        }
        return true;
    }

    // 删除安装文件
    private function extModuleScriptClean($moduleName, $manifest)
    {
        $moduleDir = IA_ROOT . '/app/' . $moduleName . '/packages/';
        $manifest['install'] = trim($manifest['install']);
        $manifest['uninstall'] = trim($manifest['uninstall']);
        $manifest['upgrade'] = trim($manifest['upgrade']);
        if (strexists($manifest['install'], '.php')) {
            if (file_exists($moduleDir . $manifest['install'])) {
                unlink($moduleDir . $manifest['install']);
            }
        }
        if (strexists($manifest['uninstall'], '.php')) {
            if (file_exists($moduleDir . $manifest['uninstall'])) {
                unlink($moduleDir . $manifest['uninstall']);
            }
        }
        if (strexists($manifest['upgrade'], '.php')) {
            if (file_exists($moduleDir . $manifest['upgrade'])) {
                unlink($moduleDir . $manifest['upgrade']);
            }
        }
        if (file_exists($moduleDir . 'manifest.xml')) {
            unlink($moduleDir . 'manifest.xml');
        }
    }

    // 解析XML文件
    private function extModuleManifestParse($xml)
    {
        if (!strexists($xml, '<manifest')) {
            $xml = base64_decode($xml);
        }
        if (empty($xml)) {
            return [];
        }
        $dom = new DOMDocument();
        $dom->loadXML($xml);
        $root = $dom->getElementsByTagName('manifest')->item(0);
        if (empty($root)) {
            return [];
        }

        $vcode = explode(',', $root->getAttribute('versionCode'));
        $manifest['versions'] = [];
        if (is_array($vcode)) {
            foreach ($vcode as $v) {
                $v = trim($v);
                if (!empty($v)) {
                    $manifest['versions'][] = $v;
                }
            }
            // $manifest['versions'][] = '2.0';
            $manifest['versions'] = array_unique($manifest['versions']);
        }


        $manifest['install'] = $root->getElementsByTagName('install')->item(0)->textContent;
        $manifest['uninstall'] = $root->getElementsByTagName('uninstall')->item(0)->textContent;
        $manifest['upgrade'] = $root->getElementsByTagName('upgrade')->item(0)->textContent;
        $application = $root->getElementsByTagName('application')->item(0);


        if (empty($application)) {
            return [];
        }
        $manifest['application'] = [
            'name'        => trim($application->getElementsByTagName('name')->item(0)->textContent),
            'identifie'   => trim($application->getElementsByTagName('identifie')->item(0)->textContent),
            'version'     => trim($application->getElementsByTagName('version')->item(0)->textContent),
            'type'        => trim($application->getElementsByTagName('type')->item(0)->textContent),
            'ability'     => trim($application->getElementsByTagName('ability')->item(0)->textContent),
            'description' => trim($application->getElementsByTagName('description')->item(0)->textContent),
            'author'      => trim($application->getElementsByTagName('author')->item(0)->textContent),
            'url'         => trim($application->getElementsByTagName('url')->item(0)->textContent),
            'setting'     => trim($application->getAttribute('setting')) == 'true',
        ];

        $platform = $root->getElementsByTagName('platform')->item(0);

        if (!empty($platform)) {
            $manifest['platform'] = [
                'supports' => [],
            ];

            $supports = $platform->getElementsByTagName('supports')->item(0);
            if (!empty($supports)) {
                $support_type = $supports->getElementsByTagName('item');
                for ($i = 0; $i < $support_type->length; $i++) {
                    $t = $support_type->item($i)->getAttribute('type');
                    if (!empty($t)) {
                        $manifest['platform']['supports'][] = $t;
                    }
                }
            }
        }
        return $manifest;
    }

    /**
     * 分割sql语句
     * @param string $content sql内容
     * @param bool $string 如果为真，则只返回一条sql语句，默认以数组形式返回
     * @param array $replace 替换前缀，如：['my_' => 'me_']，表示将表前缀my_替换成me_
     * @return array|string 除去注释之后的sql语句数组或一条语句
     */
    private function sqlParse($content = '', $string = false, $replace = [])
    {
        // 被替换的前缀
        $from = '';
        // 要替换的前缀
        $to = '';

        // 替换表前缀
        if (!empty($replace)) {
            $to = current($replace);
            $from = current(array_flip($replace));
        }

        if ($content != '') {
            // 纯sql内容
            $pure_sql = [];

            // 多行注释标记
            $comment = false;

            // 按行分割，兼容多个平台
            $content = str_replace(["\r\n", "\r"], "\n", $content);
            $content = explode("\n", trim($content));

            // 循环处理每一行
            foreach ($content as $key => $line) {
                // 跳过空行
                if ($line == '') {
                    continue;
                }

                // 跳过以#或者--开头的单行注释
                if (preg_match("/^(#|--)/", $line)) {
                    continue;
                }

                // 跳过以/**/包裹起来的单行注释
                if (preg_match("/^\/\*(.*?)\*\//", $line)) {
                    continue;
                }

                // 多行注释开始
                if (substr($line, 0, 2) == '/*') {
                    $comment = true;
                    continue;
                }

                // 多行注释结束
                if (substr($line, -2) == '*/') {
                    $comment = false;
                    continue;
                }

                // 多行注释没有结束，继续跳过
                if ($comment) {
                    continue;
                }

                // 替换表前缀
                if ($from != '') {
                    $line = str_replace('`' . $from, '`' . $to, $line);
                }

                // sql语句
                array_push($pure_sql, $line);
            }

            // 只返回一条语句
            if ($string) {
                return implode($pure_sql, "");
            }
            // 以数组形式返回sql语句
            $pure_sql = implode("\n", $pure_sql);
            $pure_sql = explode(";\n", $pure_sql);
            return $pure_sql;
        } else {
            return $string == true ? '' : [];
        }
    }
}