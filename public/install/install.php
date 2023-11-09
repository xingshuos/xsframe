<?php
error_reporting(0);
@set_time_limit(0);
ob_start();
header('content-type: text/html; charset=utf-8');
define('PATH_ROOT', str_replace("\\", '/', dirname(dirname(__FILE__))));
$actions = array('license', 'env', 'db', 'finish');
$action  = $_COOKIE['action'];
$action  = in_array($action, $actions) ? $action : 'license';
$ispost  = strtolower($_SERVER['REQUEST_METHOD']) == 'post';

if (file_exists(PATH_ROOT . '/install/install.lock') && $action != 'finish') {
    @header("Content-type: text/html; charset=UTF-8");
    echo "系统已经安装过了，如果要重新安装，那么请删除public/install目录下的install.lock文件";
    exit;
}

if ($_GET['step'] == 'get_dblist') {
    $link = @mysqli_connect($_GET['db_host'], $_GET['db_user'], $_GET['db_pass']);
    if (mysqli_connect_errno()) {
        $error = mysqli_connect_errno();
        if (strpos($error, '1045') !== false) {
            $result = array('code' => '100', 'msg' => '您的数据库访问用户名或是密码错误');
        }
    } else {
        $sql    = "SELECT `SCHEMA_NAME` FROM `information_schema`.`SCHEMATA`";
        $result = mysqli_query($link, $sql);
        while ($rs = mysqli_fetch_array($result)) {
            $databases[] = $rs[0];
        }
        $result = array('code' => '200', 'data' => implode(',', $databases));
    }
    echo json_encode($result);
    die;
}
/**
 * 安装第一步，许可协议
 */
if ($action == 'license') {
    if ($ispost) {
        setcookie('action', 'env');
        header('location: ?refresh');
        exit;
    }
    tpl_install_license();
}

/**
 *检测安装环境
 */
if ($action == 'env') {
    if ($ispost) {
        setcookie('action', $_POST['do'] == 'continue' ? 'db' : 'license');
        header('location: ?refresh');
        exit;
    }

    $ret                          = array();
    $ret['server']['os']['value'] = php_uname();
    // $ret['server']['os']['value'] = mb_convert_encoding($ret['server']['os']['value'], "UTF-8", "CP936");
    $ret['server']['os']['value'] = iconv("CP936", "UTF-8", $ret['server']['os']['value']);

    if (PHP_SHLIB_SUFFIX == 'dll') {
        $ret['server']['os']['remark'] = '建议使用 Linux 系统以提升程序性能';
        $ret['server']['os']['class']  = 'warning';
    }
    $ret['server']['sapi']['value'] = $_SERVER['SERVER_SOFTWARE'];
    if (PHP_SAPI == 'isapi') {
        $ret['server']['sapi']['remark'] = '建议使用 Apache 或 Nginx 以提升程序性能';
        $ret['server']['sapi']['class']  = 'warning';
    }
    $ret['server']['php']['value'] = PHP_VERSION;
    $ret['server']['dir']['value'] = dirname(PATH_ROOT);
    if (function_exists('disk_free_space')) {
        $ret['server']['disk']['value'] = floor(disk_free_space(PATH_ROOT) / (1024 * 1024)) . 'M';
    } else {
        $ret['server']['disk']['value'] = 'unknow';
    }
    $ret['server']['upload']['value'] = @ini_get('file_uploads') ? ini_get('upload_max_filesize') : 'unknow';

    $ret['php']['version']['value'] = PHP_VERSION;
    $ret['php']['version']['class'] = 'success';
    if (version_compare(PHP_VERSION, '5.5.0') == -1) {
        $ret['php']['version']['class']  = 'danger';
        $ret['php']['version']['failed'] = true;
        $ret['php']['version']['remark'] = 'PHP版本必须为 5.5.0 以上.';
    }

    $ret['php']['mysql']['ok'] = function_exists('mysqli_connect');
    if ($ret['php']['mysql']['ok']) {
        $ret['php']['mysql']['value'] = '<span class="icon icon-ok text-success"></span>';
    } else {
        $ret['php']['pdo']['failed']  = true;
        $ret['php']['mysql']['value'] = '<span class="icon icon-remove text-danger"></span>';
    }

    $ret['php']['pdo']['ok'] = extension_loaded('pdo') && extension_loaded('pdo_mysql');
    if ($ret['php']['pdo']['ok']) {
        $ret['php']['pdo']['value'] = '<span class="icon icon-ok text-success"></span>';
        $ret['php']['pdo']['class'] = 'success';
        if (!$ret['php']['mysql']['ok']) {
            $ret['php']['pdo']['remark'] = '您的PHP环境不支持 mysqli_connect，请开启此扩展. ';
        }
    } else {
        $ret['php']['pdo']['failed'] = true;
        if ($ret['php']['mysql']['ok']) {
            $ret['php']['pdo']['value']  = '<span class="icon icon-remove text-warning"></span>';
            $ret['php']['pdo']['class']  = 'warning';
            $ret['php']['pdo']['remark'] = '您的PHP环境不支持PDO, 请开启此扩展. ';
        } else {
            $ret['php']['pdo']['value']  = '<span class="icon icon-remove text-danger"></span>';
            $ret['php']['pdo']['class']  = 'danger';
            $ret['php']['pdo']['remark'] = '您的PHP环境不支持PDO, 也不支持 mysqli_connect, 系统无法正常运行. ';
        }
    }

    $ret['php']['fopen']['ok'] = @ini_get('allow_url_fopen') && function_exists('fsockopen');
    if ($ret['php']['fopen']['ok']) {
        $ret['php']['fopen']['value'] = '<span class="icon icon-ok text-success"></span>';
    } else {
        $ret['php']['fopen']['value'] = '<span class="icon icon-remove text-danger"></span>';
    }

    $ret['php']['curl']['ok'] = extension_loaded('curl') && function_exists('curl_init');
    if ($ret['php']['curl']['ok']) {
        $ret['php']['curl']['value'] = '<span class="icon icon-ok text-success"></span>';
        $ret['php']['curl']['class'] = 'success';
        if (!$ret['php']['fopen']['ok']) {
            $ret['php']['curl']['remark'] = '您的PHP环境虽然不支持 allow_url_fopen, 但已经支持了cURL, 这样系统是可以正常高效运行的, 不需要额外处理. ';
        }
    } else {
        if ($ret['php']['fopen']['ok']) {
            $ret['php']['curl']['value']  = '<span class="icon icon-remove text-warning"></span>';
            $ret['php']['curl']['class']  = 'warning';
            $ret['php']['curl']['remark'] = '您的PHP环境不支持cURL, 但支持 allow_url_fopen, 这样系统虽然可以运行, 但还是建议你开启cURL以提升程序性能和系统稳定性. ';
        } else {
            $ret['php']['curl']['value']  = '<span class="icon icon-remove text-danger"></span>';
            $ret['php']['curl']['class']  = 'danger';
            $ret['php']['curl']['remark'] = '您的PHP环境不支持cURL, 也不支持 allow_url_fopen, 系统无法正常运行. ';
            $ret['php']['curl']['failed'] = true;
        }
    }
    $ret['php']['gd']['ok'] = extension_loaded('gd');
    if ($ret['php']['gd']['ok']) {
        $ret['php']['gd']['value'] = '<span class="icon icon-ok text-success"></span>';
        $ret['php']['gd']['class'] = 'success';
    } else {
        $ret['php']['gd']['value']  = '<span class="icon icon-remove text-danger"></span>';
        $ret['php']['gd']['class']  = 'danger';
        $ret['php']['gd']['failed'] = true;
        $ret['php']['gd']['remark'] = '没有启用GD, 将无法正常上传和压缩图片, 系统无法正常运行. ';
    }

    $ret['php']['openssl']['ok'] = extension_loaded('openssl');
    if ($ret['php']['openssl']['ok']) {
        $ret['php']['openssl']['value'] = '<span class="icon icon-ok text-success"></span>';
        $ret['php']['openssl']['class'] = 'success';
    } else {
        $ret['php']['openssl']['value']  = '<span class="icon icon-remove text-danger"></span>';
        $ret['php']['openssl']['class']  = 'danger';
        $ret['php']['openssl']['failed'] = true;
        $ret['php']['openssl']['remark'] = '没有启用openssl扩展. ';
    }

    $ret['php']['bcmath']['ok'] = extension_loaded('bcmath');
    if ($ret['php']['bcmath']['ok']) {
        $ret['php']['bcmath']['value'] = '<span class="icon icon-ok text-success"></span>';
        $ret['php']['bcmath']['class'] = 'success';
    } else {
        $ret['php']['bcmath']['value']  = '<span class="icon icon-remove text-danger"></span>';
        $ret['php']['bcmath']['class']  = 'danger';
        $ret['php']['bcmath']['failed'] = true;
        $ret['php']['bcmath']['remark'] = '没有启用bcmath扩展. ';
    }

    $ret['php']['dom']['ok'] = class_exists('DOMDocument');
    if ($ret['php']['dom']['ok']) {
        $ret['php']['dom']['value'] = '<span class="icon icon-ok text-success"></span>';
        $ret['php']['dom']['class'] = 'success';
    } else {
        $ret['php']['dom']['value']  = '<span class="icon icon-remove text-danger"></span>';
        $ret['php']['dom']['class']  = 'danger';
        $ret['php']['dom']['failed'] = true;
        $ret['php']['dom']['remark'] = '没有启用DOMDocument, 将无法正常安装使用模块, 系统无法正常运行. ';
    }

    $ret['php']['session']['ok'] = ini_get('session.auto_start');
    if ($ret['php']['session']['ok'] == 0 || strtolower($ret['php']['session']['ok']) == 'off') {
        $ret['php']['session']['value'] = '<span class="icon icon-ok text-success"></span>';
        $ret['php']['session']['class'] = 'success';
    } else {
        $ret['php']['session']['value']  = '<span class="icon icon-remove text-danger"></span>';
        $ret['php']['session']['class']  = 'danger';
        $ret['php']['session']['failed'] = true;
        $ret['php']['session']['remark'] = '系统session.auto_start开启, 将无法正常注册会员, 系统无法正常运行. ';
    }

    $ret['php']['asp_tags']['ok'] = ini_get('asp_tags');
    if (empty($ret['php']['asp_tags']['ok']) || strtolower($ret['php']['asp_tags']['ok']) == 'off') {
        $ret['php']['asp_tags']['value'] = '<span class="icon icon-ok text-success"></span>';
        $ret['php']['asp_tags']['class'] = 'success';
    } else {
        $ret['php']['asp_tags']['value']  = '<span class="icon icon-remove text-danger"></span>';
        $ret['php']['asp_tags']['class']  = 'danger';
        $ret['php']['asp_tags']['failed'] = true;
        $ret['php']['asp_tags']['remark'] = '请禁用可以使用ASP 风格的标志，配置php.ini中asp_tags = Off';
    }

    $ret['write']['root']['ok'] = local_writeable(PATH_ROOT . '/uploads');
    if ($ret['write']['root']['ok']) {
        $ret['write']['root']['value'] = '<span class="icon icon-ok text-success"></span>';
        $ret['write']['root']['class'] = 'success';
    } else {
        $ret['write']['root']['value']  = '<span class="icon icon-remove text-danger"></span>';
        $ret['write']['root']['class']  = 'danger';
        $ret['write']['root']['failed'] = true;
        $ret['write']['root']['remark'] = 'public/uploads无法写入, 将无法使用自动更新功能, 系统无法正常运行.  ';
    }
    $ret['write']['data']['ok'] = local_writeable(PATH_ROOT . '/../runtime');
    if ($ret['write']['data']['ok']) {
        $ret['write']['data']['value'] = '<span class="icon icon-ok text-success"></span>';
        $ret['write']['data']['class'] = 'success';
    } else {
        $ret['write']['data']['value']  = '<span class="icon icon-remove text-danger"></span>';
        $ret['write']['data']['class']  = 'danger';
        $ret['write']['data']['failed'] = true;
        $ret['write']['data']['remark'] = 'runtime目录无法写入, 将无法写入配置文件, 系统无法正常安装. ';
    }
    $ret['write']['install']['ok'] = local_writeable(PATH_ROOT . '/install');
    if ($ret['write']['install']['ok']) {
        $ret['write']['install']['value'] = '<span class="icon icon-ok text-success"></span>';
        $ret['write']['install']['class'] = 'success';
    } else {
        $ret['write']['install']['value']  = '<span class="icon icon-remove text-danger"></span>';
        $ret['write']['install']['class']  = 'danger';
        $ret['write']['install']['failed'] = true;
        $ret['write']['install']['remark'] = 'public/install目录无法写入, 将无法写入安装文件, 系统无法正常安装. ';
    }

    if( !is_file(dirname(PATH_ROOT) . '/.env') ){
        @file_put_contents(dirname(PATH_ROOT) . '/.env');
    }

    $ret['write']['database']['ok'] = is_writable(dirname(PATH_ROOT) . '/.env');
    if ($ret['write']['database']['ok']) {
        $ret['write']['database']['value'] = '<span class="icon icon-ok text-success"></span>';
        $ret['write']['database']['class'] = 'success';
    } else {
        $ret['write']['database']['value']  = '<span class="icon icon-remove text-danger"></span>';
        $ret['write']['database']['class']  = 'danger';
        $ret['write']['database']['failed'] = true;
        $ret['write']['database']['remark'] = '.env文件无法写入, 将无法写入数据库配置文件, 系统无法正常安装. ';
    }

    $ret['continue'] = true;
    foreach ($ret['php'] as $opt) {
        if ($opt['failed']) {
            $ret['continue'] = false;
            break;
        }
    }
    foreach ($ret['write'] as $opt) {
        if ($opt['failed']) {
            $ret['continue'] = false;
            break;
        }
    }
    tpl_install_env($ret);
}

/**
 *配置数据库信息
 */
if ($action == 'db') {
    if ($ispost) {
        if ($_POST['do'] != 'continue') {
            setcookie('action', 'env');
            header('location: ?refresh');
            exit();
        }
        $db    = $_POST['db'];
        $user  = $_POST['user'];
        $store = $_POST['store'];

        $link = @mysqli_connect($db['server'], $db['username'], $db['password']);
        if (mysqli_connect_errno()) {
            $error = mysqli_connect_errno();
            if (strpos($error, '1045') !== false) {
                $error = '您的数据库访问用户名或是密码错误. <br />';
            }
        } else {
            mysqli_query($link, "SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
            mysqli_query($link, "SET sql_mode=''");
            if (mysqli_errno($link)) {
                $error = mysqli_error($link);
            } else {
                $query = mysqli_query($link, "SHOW DATABASES LIKE  '{$db['name']}';");
                if (!mysqli_fetch_assoc($query)) {
                    if (mysqli_get_server_info($link) > '4.1') {
                        mysqli_query($link, "CREATE DATABASE IF NOT EXISTS `{$db['name']}` DEFAULT CHARACTER SET utf8");
                    } else {
                        mysqli_query($link, "CREATE DATABASE IF NOT EXISTS `{$db['name']}`");
                    }
                }
                $query = mysqli_query($link, "SHOW DATABASES LIKE  '{$db['name']}';");
                if (!mysqli_fetch_assoc($query)) {
                    $error .= "数据库不存在且创建数据库失败. <br />";
                }
                if (mysqli_errno($link)) {
                    $error .= mysqli_error($link);
                }
            }
        }

        if (empty($error)) {
            $pieces     = explode(':', $db['server']);
            $db['port'] = !empty($pieces[1]) ? $pieces[1] : '3306';
            $config     = db_config();
            $config     = str_replace(array(
                '{db-server}', '{db-username}', '{db-password}', '{db-port}', '{db-name}', '{db-prefix}'
            ), array(
                $db['server'], $db['username'], $db['password'], $db['port'], $db['name'], $db['prefix']
            ), $config);

            mysqli_close($link);

            $link = mysqli_connect($db['server'], $db['username'], $db['password']);
            mysqli_select_db($link, $db['name']);
            mysqli_query($link, "SET character_set_connection=utf8, character_set_results=utf8, character_set_client=binary");
            mysqli_query($link, "SET sql_mode=''");

            //循环添加数据
            if (file_exists(PATH_ROOT . '/install/install.sql')) {
                $sql = file_get_contents(PATH_ROOT . '/install/install.sql');

                $sql = str_replace('#__', $db['prefix'], $sql);
                $sql = str_replace("\r", "\n", $sql);
                $sql = explode(";\n", $sql);

                foreach ($sql as $item) {
                    $item = trim($item);
                    if (empty($item))
                        continue;
                    preg_match('/CREATE TABLE `([^ ]*)`/', $item, $matches);
                    if ($matches) {
                        $table_name = $matches[1];
                        mysqli_query($link, $item);
                    } else {
                        mysqli_query($link, $item);
                    }
                }
            } else {
                die('<script type="text/javascript">alert("安装包不正确, 数据安装脚本缺失.");history.back();</script>');
            }

            //添加用户管理员
            $curTime      = time();
            $authkey      = local_salt(6);
            $password     = md5($user['password'] . $authkey);
            $insert_error = mysqli_query($link, "INSERT  INTO `{$db['prefix']}sys_users`(`username`,`password`,`salt`,`role`,`status`,`createtime`,`logintime`,`lastip`) values ('admin','{$password}','{$authkey}','owner',1,{$curTime},{$curTime},'127.0.0.1')");
            if (!$insert_error) {
                die('<script type="text/javascript">alert("管理员账户注册失败.");history.back();</script>');
            }

            //配置数据库
            file_put_contents(dirname(PATH_ROOT) . '/.env', $config);

            //生成辨识
            @touch(PATH_ROOT . '/install/install.lock');

            setcookie('action', 'finish');
            header('location: ?refresh');
            exit();

        }
    }
    tpl_install_db($error);
}

/**
 * 安装完成
 */
if ($action == 'finish') {
    setcookie('action', '', time() - 3600);
    $sitepath = strtolower(substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'], '/')));
    $sitepath = str_replace('/install', "", $sitepath);
    $url      = strtolower('http://' . $_SERVER['HTTP_HOST'] . $sitepath);
    tpl_install_finish($url);
}

function tpl_install_license()
{
    echo <<<EOF
		<div class="panel panel-default">
			<div class="panel-heading">阅读许可协议</div>
			<div class="panel-body" style="overflow-y:scroll;max-height:400px;line-height:20px;">
				<h3>版权所有 (c)2020，星数（xsframe）团队保留所有权利。 </h3>
				<p>
					感谢您选择星数（xsframe）（以下简称xsframe，xsframe基于 PHP + MySQL的技术开发，全部源码开放。 <br />
					为了使你正确并合法的使用本软件，请你在使用前务必阅读清楚下面的协议条款：
				</p>
				<p>
					<strong>一、本授权协议适用且仅适用于星数（xsframe）系统(以下简称xsframe)任何版本，星数（xsframe）官方对本授权协议的最终解释权。</strong>
				</p>
				<p>
					<strong>二、协议许可的权利 </strong>
					<ol>
						<li>您可以在完全遵守本最终用户授权协议的基础上，将本软件应用于非商业用途，而不必支付软件版权授权费用。</li>
						<li>您可以在协议规定的约束和限制范围内修改星数（xsframe）源代码或界面风格以适应您的网站要求。</li>
						<li>您拥有使用本软件构建的网站全部内容所有权，并独立承担与这些内容的相关法律义务。</li>
						<li>获得商业授权之后，您可以将本软件应用于商业用途，同时依据所购买的授权类型中确定的技术支持内容，自购买时刻起，在技术支持期限内拥有通过指定的方式获得指定范围内的技术支持服务。商业授权用户享有反映和提出意见的权力，相关意见将被作为首要考虑，但没有一定被采纳的承诺或保证。</li>
					</ol>
				</p>
				<p>
					<strong>三、协议规定的约束和限制 </strong>
					<ol>
						<li>未获商业授权之前，不得将本软件用于商业用途（包括但不限于企业网站、经营性网站、以营利为目的或实现盈利的网站）。</li>
						<li>未经官方许可，不得对本软件或与之关联的商业授权进行出租、出售、抵押或发放子许可证。</li>
						<li>未经官方许可，禁止在星数（xsframe）的整体或任何部分基础上以发展任何派生版本、修改版本或第三方版本用于重新分发。</li>
						<li>如果您未能遵守本协议的条款，您的授权将被终止，所被许可的权利将被收回，并承担相应法律责任。</li>
					</ol>
				</p>
				<p>
					<strong>四、有限担保和免责声明 </strong>
					<ol>
						<li>本软件及所附带的文件是作为不提供任何明确的或隐含的赔偿或担保的形式提供的。</li>
						<li>用户出于自愿而使用本软件，您必须了解使用本软件的风险，在尚未购买产品技术服务之前，我们不承诺对免费用户提供任何形式的技术支持、使用担保，也不承担任何因使用本软件而产生问题的相关责任。</li>
						<li>电子文本形式的授权协议如同双方书面签署的协议一样，具有完全的和等同的法律效力。您一旦开始确认本协议并安装  xsframe，即被视为完全理解并接受本协议的各项条款，在享有上述条款授予的权力的同时，受到相关的约束和限制。协议许可范围以外的行为，将直接违反本授权协议并构成侵权，我们有权随时终止授权，责令停止损害，并保留追究相关责任的权力。</li>
						<li>如果本软件带有其它软件的整合API示范例子包，这些文件版权不属于本软件官方，并且这些文件是没经过授权发布的，请参考相关软件的使用许可合法的使用。</li>
					</ol>
				</p>
			</div>
		</div>
		<form class="form-inline" role="form" method="post">
			<ul class="pager">
				<li class="pull-left" style="display:block;padding:5px 10px 5px 0;">
					<div class="checkbox">
						<label>
							<input type="checkbox"> 我已经阅读并同意此协议
						</label>
					</div>
				</li>
				<li class="previous"><a href="javascript:;" onclick="if(jQuery(':checkbox:checked').length == 1){jQuery('form')[0].submit();}else{alert('您必须同意软件许可协议才能安装！')};">继续 <span class="icon icon-chevron-right"></span></a></li>
			</ul>
		</form>
EOF;
    tpl_frame();
}

function tpl_frame()
{
    global $action, $actions;
    $action = $_COOKIE['action'];
    $step   = array_search($action, $actions);
    $steps  = array();
    for ($i = 0; $i <= $step; $i++) {
        if ($i == $step) {
            $steps[$i] = ' list-group-item-info';
        } else {
            $steps[$i] = ' list-group-item-success';
        }
    }
    $progress = $step * 25 + 25;
    $content  = ob_get_contents();
    ob_clean();
    $tpl = <<<EOF
<!DOCTYPE html>
<html lang="zh-cn">
	<head>
		<meta charset="utf-8">
		<meta http-equiv="X-UA-Compatible" content="IE=edge">
		<meta name="viewport" content="width=device-width, initial-scale=1">
		<title>安装系统 - 星数PHP软件开发系统</title>

		<script src="../app/admin/static/components/jquery/jquery-1.11.1.min.js"></script>
		<link rel="stylesheet" href="../app/admin/static/components/bootstrap/bootstrap.min.css">
		<link rel="stylesheet" href="../app/admin/static/fonts/awesome/font-awesome.min.css?v=3.2.1"></link>
        <link rel="stylesheet" href="../app/admin/static/css/common.css">
        
		<style>
			html,body{font-size:13px;font-family:"Microsoft YaHei UI", "微软雅黑", "宋体";}
			.pager li.previous a{height: 30px;margin-right:10px;display: flex;flex-direction: row;justify-content: center;align-items: center;}
			.header a{color:#FFF;}
			.header a:hover{color:#2b85e4;}
			.footer{padding:10px;}
			.footer a,.footer{color:#eee;font-size:14px;line-height:25px;}
			.panel-heading{padding:15px 20px;}
			.list-group-item{padding:15px 20px;}
			ol li{line-height: 30px}
			
			.header{
			    width: 100%;
			    height: 60px;
			    background-color: #393D49 !important;
			}
			.footer{
			    background-color: #393D49 !important;
			    padding: 50px 0;
                box-sizing: border-box;
			}
			.header .nav-bar{
                display: flex;
                flex-direction: row;
                justify-content: space-between;
                align-items: center;
			}
			.header .nav-bar .logo-box{
			    width: 130px;
			}
			.header .nav-bar .logo-box .name{
			    font-size: 16px;
			    color: #ffffff;
			}
			.header .nav-bar .nav-pills{
			    flex: 1;
                display: flex;
                flex-direction: row;
                justify-content: flex-end;
                align-items: center;
			}
			.container-box{
			    width: 100%;
			    padding: 30px;
			    box-sizing: border-box;
			}
		</style>
	</head>
	<body style="background-color:#fff;">
        
        <div class="header">
            <div class="container nav-bar" style="height: 100%;">
                <div class="logo-box">
                    <div class="name">星数（xsframe）</div>
                </div>
                <ul class="nav nav-pills pull-right" role="tablist">
                    <li role="presentation" class="active"><a href="javascript:;">安装星数</a></li>
                    <li role="presentation"><a target = "_blank" href="//s.xsframe.com">星数官网</a></li>
                    <li role="presentation"><a target = "_blank" href="//s.xsframe.com">应用商城</a></li>
                    <li role="presentation"><a target = "_blank" href="//doc.xsframe.com">开发文档</a></li>
                    <li role="presentation"><a target = "_blank" href="//bbs.xsframe.com">开源论坛</a></li>
                    <li role="presentation"><a target="_blank" href="https://qm.qq.com/cgi-bin/qm/qr?k=w-rIcxUJfiPmEm6kwWf0iqcUUkxGRw8-&jump_from=webapi"><img border="0" src="//pub.idqqimg.com/wpa/images/group.png" alt="星数开发者交流群" title="星数开发者交流群"></a></li>
                </ul>
            </div>
        </div>
        
        <div class="container-box">
            <div class="container">
                <div class="row well">
                    <div class="col-xs-3">
                        <div class="progress" title="安装进度">
                            <div class="progress-bar progress-bar-info progress-bar-striped active" role="progressbar" aria-valuenow="{$progress}" aria-valuemin="0" aria-valuemax="100" style="width: {$progress}%;">
                                {$progress}%
                            </div>
                        </div>
                        <div class="panel panel-default">
                            <div class="panel-heading">
                                安装步骤
                            </div>
                            <ul class="list-group">
                                <a href="javascript:;" class="list-group-item{$steps[0]}"><span class="icon icon-bookmark"></span> &nbsp; 许可协议</a>
                                <a href="javascript:;" class="list-group-item{$steps[1]}"><span class="icon icon-eye-open"></span> &nbsp; 环境监测</a>
                                <a href="javascript:;" class="list-group-item{$steps[2]}"><span class="icon icon-cog"></span> &nbsp; 参数配置</a>
                                <a href="javascript:;" class="list-group-item{$steps[3]}"><span class="icon icon-ok"></span> &nbsp; 成功</a>
                            </ul>
                        </div>
                    </div>
                    <div class="col-xs-9">
                        {$content}
                    </div>
                </div>
            </div>
		</div>
		
		<div class="footer" style="margin:15px auto;">
            <div class="text-center">
                <a  target = "_blank" href="//www.xsframe.com">星数官网</a> &nbsp; &nbsp; 
                <a  target = "_blank" href="//s.xsframe.com">星数应用商城</a> &nbsp; &nbsp; 
                <a  target = "_blank" href="//bbs.xsframe.com">访问星数开源论坛</a> &nbsp; &nbsp; 
                <a target = "_blank" href="//doc.xsframe.com">开发文档</a>
                <a target="_blank" href="https://qm.qq.com/cgi-bin/qm/qr?k=w-rIcxUJfiPmEm6kwWf0iqcUUkxGRw8-&jump_from=webapi" style="margin-left: 12px">
                    <img border="0" src="//pub.idqqimg.com/wpa/images/group.png" alt="星数开发者交流群" title="星数开发者交流群">
                </a>
            </div>
            <div class="text-center">
                Powered by <a  target = "_blank" href="https://www.xsframe.com"><b>星数</b></a>  &copy; 2013-2022 <a target = "_blank" href="">www.xsframe.com</a>
            </div>
        </div>
	</body>
</html>
EOF;
    echo trim($tpl);
}

function local_writeable($dir)
{
    $writeable = 0;
    if (!is_dir($dir)) {
        @mkdir($dir, 0777);
    }
    if (is_dir($dir)) {
        if ($fp = fopen("$dir/test.txt", 'w')) {
            fclose($fp);
            unlink("$dir/test.txt");
            $writeable = 1;
        } else {
            $writeable = 0;
        }
    }
    return $writeable;
}

function tpl_install_env($ret = array())
{
    if (!$ret['continue']) {
        $continue = '<li class="previous disabled"><a href="javascript:;">请先解决环境问题后继续</a></li>';
    } else {
        $continue = '<li class="previous"><a href="javascript:;" onclick="$(\'#do\').val(\'continue\');$(\'form\')[0].submit();">继续 <span class="icon icon-chevron-right"></span></a></li>';
    }
    echo <<<EOF
		<div class="panel panel-default">
			<div class="panel-heading">服务器信息</div>
			<table class="table table-striped">
				<tr>
					<th style="width:150px;">参数</th>
					<th>值</th>
					<th></th>
				</tr>
				<tr class="{$ret['server']['os']['class']}">
					<td>服务器操作系统</td>
					<td>{$ret['server']['os']['value']}</td>
					<td>{$ret['server']['os']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['sapi']['class']}">
					<td>Web服务器环境</td>
					<td>{$ret['server']['sapi']['value']}</td>
					<td>{$ret['server']['sapi']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['php']['class']}">
					<td>PHP版本</td>
					<td>{$ret['server']['php']['value']}</td>
					<td>{$ret['server']['php']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['dir']['class']}">
					<td>程序安装目录</td>
					<td>{$ret['server']['dir']['value']}</td>
					<td>{$ret['server']['dir']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['disk']['class']}">
					<td>磁盘空间</td>
					<td>{$ret['server']['disk']['value']}</td>
					<td>{$ret['server']['disk']['remark']}</td>
				</tr>
				<tr class="{$ret['server']['upload']['class']}">
					<td>上传限制</td>
					<td>{$ret['server']['upload']['value']}</td>
					<td>{$ret['server']['upload']['remark']}</td>
				</tr>
			</table>
		</div>

		<div class="alert alert-info">PHP环境要求必须满足下列所有条件，否则系统或系统部份功能将无法使用。</div>
		<div class="panel panel-default">
			<div class="panel-heading">PHP环境要求</div>
			<table class="table table-striped">
				<tr>
					<th style="width:150px;">选项</th>
					<th style="width:250px;">要求</th>
					<th style="width:50px;">状态</th>
					<th>说明及帮助</th>
				</tr>
				<tr class="{$ret['php']['version']['class']}">
					<td>PHP版本</td>
					<td>5.5或者5.5以上</td>
					<td>{$ret['php']['version']['value']}</td>
					<td>{$ret['php']['version']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['pdo']['class']}">
					<td>MySQL</td>
					<td>支持(建议支持PDO)</td>
					<td>{$ret['php']['mysql']['value']}</td>
					<td rowspan="2">{$ret['php']['pdo']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['pdo']['class']}">
					<td>PDO_MYSQL</td>
					<td>支持(强烈建议支持)</td>
					<td>{$ret['php']['pdo']['value']}</td>
				</tr>
				<tr class="{$ret['php']['curl']['class']}">
					<td>allow_url_fopen</td>
					<td>支持(建议支持cURL)</td>
					<td>{$ret['php']['fopen']['value']}</td>
					<td rowspan="2">{$ret['php']['curl']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['curl']['class']}">
					<td>cURL</td>
					<td>支持(强烈建议支持)</td>
					<td>{$ret['php']['curl']['value']}</td>
				</tr>
				<tr class="{$ret['php']['gd']['class']}">
					<td>GD2</td>
					<td>支持</td>
					<td>{$ret['php']['gd']['value']}</td>
					<td>{$ret['php']['gd']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['openssl']['class']}">
					<td>openssl</td>
					<td>支持</td>
					<td>{$ret['php']['openssl']['value']}</td>
					<td>{$ret['php']['openssl']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['bcmath']['class']}">
					<td>bcmath</td>
					<td>支持</td>
					<td>{$ret['php']['bcmath']['value']}</td>
					<td>{$ret['php']['bcmath']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['dom']['class']}">
					<td>DOM</td>
					<td>支持</td>
					<td>{$ret['php']['dom']['value']}</td>
					<td>{$ret['php']['dom']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['session']['class']}">
					<td>session.auto_start</td>
					<td>关闭</td>
					<td>{$ret['php']['session']['value']}</td>
					<td>{$ret['php']['session']['remark']}</td>
				</tr>
				<tr class="{$ret['php']['asp_tags']['class']}">
					<td>asp_tags</td>
					<td>关闭</td>
					<td>{$ret['php']['asp_tags']['value']}</td>
					<td>{$ret['php']['asp_tags']['remark']}</td>
				</tr>
			</table>
		</div>

		<div class="alert alert-info">系统要求星数（xsframe）安装目录下的runtime和uploads必须可写, 才能使用星数（xsframe）所有功能。</div>
		<div class="panel panel-default">
			<div class="panel-heading">目录权限监测</div>
			<table class="table table-striped">
				<tr>
					<th style="width:150px;">目录</th>
					<th style="width:250px;">要求</th>
					<th style="width:50px;">状态</th>
					<th>说明及帮助</th>
				</tr>
				<tr class="{$ret['write']['root']['class']}">
					<td>/</td>
					<td>public/uploads目录可写</td>
					<td>{$ret['write']['root']['value']}</td>
					<td>{$ret['write']['root']['remark']}</td>
				</tr>
				<tr class="{$ret['write']['data']['class']}">
					<td>/</td>
					<td>runtime目录可写</td>
					<td>{$ret['write']['data']['value']}</td>
					<td>{$ret['write']['data']['remark']}</td>
				</tr>
                <tr class="{$ret['write']['install']['class']}">
					<td>/</td>
					<td>public/install目录可写</td>
					<td>{$ret['write']['install']['value']}</td>
					<td>{$ret['write']['install']['remark']}</td>
				</tr>
				<tr class="{$ret['write']['database']['class']}">
					<td>/</td>
					<td>.env文件可写</td>
					<td>{$ret['write']['database']['value']}</td>
					<td>{$ret['write']['database']['remark']}</td>
				</tr>
			</table>
		</div>
		<form class="form-inline" role="form" method="post">
			<input type="hidden" name="do" id="do" />
			<ul class="pager">
				<li class="previous"><a href="javascript:;" onclick="$('#do').val('back');$('form')[0].submit();"><span class="icon icon-chevron-left"></span> 返回</a></li>
				{$continue}
			</ul>
		</form>
EOF;
    tpl_frame();
}

function tpl_install_db($error = '')
{
    if (!empty($error)) {
        $message = '<div class="alert alert-danger">发生错误: ' . $error . '</div>';
    }
    $insTypes = array();
    if (!empty($_POST['type'])) {
        $insTypes                 = array();
        $insTypes[$_POST['type']] = ' checked="checked"';
    }
    $disabled = empty($insTypes['local']) ? ' disabled="disabled"' : '';
    echo <<<EOF
	{$message}
	<form class="form-horizontal" method="post" role="form">

		<div class="panel panel-default" style="display: none;">
			<div class="panel-heading">安装选项</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-sm-2 control-label">安装方式</label>
					<div class="col-sm-10">				
						<label class="radio-inline">
							<input type="radio" name="type" value="local"{$insTypes['local']} checked> 离线安装
						</label>					
					</div>
				</div>
			</div>
		</div>
		
		<div class="panel panel-default">
			<div class="panel-heading">数据库选项</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库主机</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[server]" value="127.0.0.1">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库用户</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[username]" value="root">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库密码</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[password]">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">表前缀</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[prefix]" value="ims_">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">数据库名称</label>
					<div class="col-sm-4">
						<input class="form-control" type="text" name="db[name]" value="xsframe" onblur="check_dblist()">
					</div>
				</div>
			</div>
		</div>
		<div class="panel panel-default">
			<div class="panel-heading">管理选项</div>
			<div class="panel-body">
				<div class="form-group">
					<label class="col-sm-2 control-label">管理员账号</label>
					<div class="col-sm-4">
						<input class="form-control" type="username" name="user[username]" value="admin">
					</div>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">管理员密码</label>
					<div class="col-sm-4">
						<input class="form-control user" type="password" name="user[password]" value="">
					</div>
					 <p class="help-block">管理员密码不少于六个字符</p>
				</div>
				<div class="form-group">
					<label class="col-sm-2 control-label">确认密码</label>
					<div class="col-sm-4">
						<input class="form-control user" type="password" value="">
					</div>
				</div>
			</div>
		</div>
		<input type="hidden" name="do" id="do" />
		<ul class="pager">
			<li class="previous"><a href="javascript:;" onclick="$('#do').val('back');$('form')[0].submit();"><span class="icon icon-chevron-left"></span> 返回</a></li>
			<li class="previous"><a href="javascript:;" onclick="if(check(this)){jQuery('#do').val('continue');if($('input[name=type]').val() == 'remote'){alert('在线线安装时，安装程序会下载精简版快速完成安装，完成后请务必注册云服务更新到完整版。')}$('form')[0].submit();}">继续 <span class="icon icon-chevron-right"></span></a></li>
		</ul>
	</form>
	<script>

	function check_dblist() {
	  params="db_host=" + $("input[name='db[server]']").val() + "&"
            + "db_user=" + $("input[name='db[username]']").val() + "&"
            + "db_pass=" + $("input[name='db[password]']").val();
	
      $.ajax({ url:'./install.php?step=get_dblist',data:params, type:'get', dataType: 'json',async:false,success:function (result) {
   
          if (typeof(result) === "object" && result["code"] === "200") {
             // console.log(result["data"].split(","));
             var list=result["data"].split(",");
              for (var i = 0; i < list.length; i++) {
            if ($("input[name='db[name]']").val() === list[i]) {
                var answer = confirm("数据库已存在，是否覆盖此数据库？");
                if (answer === false) {
                    $("input[name='db[name]']").val("");
                }
            }
        }
      }else {
        alert(result.msg);
         $("input[name='db[password]']").val("");
        return false;
    }
},
});
	}
		var lock = false;
		function check(obj) {
			if(lock) {
				return;
			}
			$('.form-control').parent().parent().removeClass('has-error');
			var error = false;
			$('.form-control').each(function(){							   
				if($(this).attr('name') != 'db[password]'){
							    
    				if($(this).val() == '') {
    					$(this).parent().parent().addClass('has-error');
    					this.focus();
    					error = true;
    				}
				}
			});
			if(error) {
				alert('请检查未填项');
				return false;
			}
            if($(':password').eq(0).val().length < 6) {
				$('.user').parent().parent().addClass('has-error');
				alert('管理员密码不少于六个字符.');
				return false;
			}
			if($(':password').eq(0).val() != $(':password').eq(1).val()) {
				$('.user').parent().parent().addClass('has-error');
				alert('确认密码不正确.');
				return false;
			}
			
			lock = true;
			$(obj).parent().addClass('disabled');
			$(obj).html('正在执行安装');
			return true;
		}
		
	</script>
EOF;
    tpl_frame();
}

function db_config()
{
    $cfg = <<<EOF
    
APP_DEBUG = false
DEFAULT_APP = admin

[CACHE]
driver=file
host={db-server}
port=6379
password=
prefix=xsframe

[DATABASE]
TYPE = mysql
HOSTNAME = {db-server}
DATABASE = {db-name}
USERNAME = {db-username}
PASSWORD = {db-password}
HOSTPORT = {db-port}
PREFIX = {db-prefix}

EOF;
    return trim($cfg);
}

function local_salt($length = 8)
{
    $result = '';
    while (strlen($result) < $length) {
        $result .= sha1(uniqid('', true));
    }
    return substr($result, 0, $length);
}

function tpl_install_finish($url = '')
{
    echo <<<EOF
	<div class="page-header"><h3>安装完成</h3></div>
	<div class="alert alert-success">
		恭喜您!已成功安装“星数（xsframe）软件开发开源系统”系统，您现在可以: <a target="_self" class="btn btn-success" href="$url/home">访问网站首页</a>
		<a target="_self" class="btn btn-success" href="$url/admin/index">访问管理后台</a>
	</div>
	<div class="form-group">
		<h5><strong>星数（xsframe）软件开发开源系统</strong></h5>		
	</div>

	
EOF;
    tpl_frame();

}