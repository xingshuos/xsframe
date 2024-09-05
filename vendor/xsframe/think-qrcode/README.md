# ThinkPHP6 二维码生成

QR code generation for thinkphp6

### 安装方法

```shell
composer require xsframe/think-qrcode
```

### 配置方法

配置文件路径：/config/qrcode.php
+ cache_dir = 生成地址
+ background = 背景图片

### 使用方法

```php
$code = new Qrcode();
$code_path = $code->png('text')                     //生成二维码
    ->logo('static/image/logo.png')                 //生成logo二维码
    ->background(180, 500)                          //给二维码加上背景
    ->text($role, 20, ['center', 740], '#ff4351')   //添加文字水印
    ->text($nick_name, 20, ['center', 780], '#000000')
    ->getPath();                                    //获取二维码生成的地址
```