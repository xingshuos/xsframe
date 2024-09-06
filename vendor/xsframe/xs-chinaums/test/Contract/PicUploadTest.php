<?php
include_once '../../vendor/autoload.php';

use xsframe\chinaums\Factory;

date_default_timezone_set('PRC');

$config = [
    'gateway' => 'https://selfapply-test.chinaums.com',
    // 'gateway' => 'https://yinshangpai.chinaums.com',
    // 商户号
    'accesser_id' => '300015',
    // 回调验证需要的md5key
    'private_key' => 'udik876ehjde32dU61edsxsf'
];
$data = [
    'request_date' => date('YmdHis'),//请求时间
    'pic_base64' => 'data:image/png;base64,iVBORw0KGgoAAAANSUhEUgAAAEgAAABICAMAAABiM0N1AAAAmVBMVEUAAAAAygEAygEAzw0A3DIAygEAygEAzQUAygEAygEAygIA0AoAygEAygEAygIAywMAzwgA1BIA/2AAygMA2RsAygEAygEAygIAywIAywQAzAQAywQAywYAygEAygEAygEAyQEAygIAygIAygIAywIAygIAzQcAygEAygEAygEAygIAygIAygIAzAYAywMAywMAzAMAygIAyQFOdEr4AAAAMnRSTlMA+/ETBdm8LtvPchjIwZhOHA0CWwnn4YZ1Rj03JvTt67GhnJGLeSLVt6t+bWcrYVdKppctVJsAAAJbSURBVFjD7dfXmqJAEIbhv8FAEAUUc85Zp+7/4lZ9lBWrEbrZPZv3cEYL+ewGxK9Chs39zLMiQ4hRtKnMV1cX6tbOdkxMe9Y0ocDdbyiNmDZKyKc/FfRV1DHzjPEo2yhzlD+lfIxuiHShM6LcatX0xhVSIVZpdSJStGtB4iJIme2DOQrS0GaTmqSn7CKhJ0iTl9yeI90PdEz0LtmkpXJF0pI0iBlbkSeNQONOXLk6f10OKqTKOsRp3Log5/mNKX9RjTCu2zXuHy/A3VYtzc8AsYZFD6tHIVJgLH3EqnGTdghgqZImQMy8xYn1AJQ/jllLvQBdQiAZJzYHhpQ0QC+SpdkNABYn1gbObBBclt9YrAEW552PBSX94OYgEodzAiAZh+nD+zwH/3HMzVuaUmJfOgZJnGHRhwXuWvXn7bAP8DhcFxN2zzKfb9m80vA4XAf8fFd4eu0nHodbSgZFnwN4HK4DQxZOolGmb/aQ/L8M5pR1qXEge0UjKw7XxII4L0cctiGa0j+zOFlM+NKbOouTwQJgEyeGGXH4MgK6JFHPiMP3LODKDip6wDUjDlswc5IQXo3yO+JuLaigyXNTzaig42vhTqgQO2RPWVrEALEdFbDCXy2btFVCvPG1M9kBEqpsku5j7SkiDdYazLCscV4uJIIpKdoFkOuorZ8DUtWo8M8s1V03PuKbvOvS2Af4xs15GeumjVHZvJNZD5nmrwDdtXld1gR7UPE6/RA5tOlOLEw8lKrN/Xxbs62y7W3rq0s1RB6v59LpEEU5tzGbHoo70/hQwr8QlPDrP/oD+JVFlunvag4AAAAASUVORK5CYII=',
    'request_seq' => uniqid(), 
];
Factory::config($config);
// $reponse = Factory::Contract()->PicUpload()->request($data);
$reponse = Factory::Contract()->PicUpload($data);
echo 'response:' . $reponse . PHP_EOL;
