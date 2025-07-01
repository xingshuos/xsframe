<?php


namespace xsframe\wrapper;

use think\facade\Env;
use xsframe\util\ArrayUtil;
use xsframe\util\FileUtil;
use think\facade\Db;
use think\Request;
use XMLWriter;
use xsframe\util\LoggerUtil;

class SiteMapWrapper
{
    private $site;
    private $get;
    private $request;
    private $settingsController;
    private $token = null;
    private $rootPath = "";

    public function __construct(Request $request)
    {
        $this->request = $request;
        $this->get = $this->request->param();
        $this->site = $request->domain();
        $this->token = Env::get('sitemap.token') ?? '';

        if (!$this->settingsController instanceof SettingsWrapper) {
            $this->settingsController = new SettingsWrapper();
        }

        $this->rootPath = str_replace("\\", '/', dirname(dirname(dirname(dirname(__FILE__)))));

        $this->init();
    }

    private function init()
    {
        $this->submitUrls();
        $this->createSiteMap();
        exit("success");
    }

    // 主动提交收录
    public function submitUrls()
    {
        $urlsPath = $this->rootPath . "/runtime/data";
        if (!is_dir($urlsPath)) {
            FileUtil::mkDirs($urlsPath);
        }

        $urlsResult = $this->getUrls();
        $urls = [];
        foreach ($urlsResult as $urlsItem) {
            $urls[] = $this->completeUrl($urlsItem['url']);
        }

        $urlsLogs = [];
        if (is_file($urlsPath . "/urls.log")) {
            $urlsLogs = file_get_contents($urlsPath . "/urls.log");
            $urlsLogs = explode("\n", $urlsLogs);
        }

        $urls = ArrayUtil::getDifferent($urls, $urlsLogs);

        if (!empty($urls)) {
            if (!empty($this->token)) {
                $apiUrl = "http://data.zz.baidu.com/urls?site={$this->site}&token=" . $this->token;
                $result = $this->httpPost($apiUrl, $urls);
                $result = json_decode($result, true);
                if ($result['error'] == 401 || $result['error'] == 400) {
                    $errorMsg = "Submission failed : " . $result['message'] . PHP_EOL . "<br>";
                    echo($errorMsg);
                    LoggerUtil::warning($errorMsg);
                } else {
                    echo("Baidu Included remain:" . $result['remain'] ?? '无' . " success:{$result['success']} " . date('Y-m-d H:i:s') . PHP_EOL . "<br>");
                }
            }

            foreach ($urls as $url) {
                file_put_contents($urlsPath . "/urls.log", $url . "\n", FILE_APPEND);
            }
        }

        return true;
    }

    // 创建网站map
    private function createSiteMap()
    {
        $this->createRobots();

        $xml = new XMLWriter();
        $xml->openMemory();
        $xml->setIndent(true);
        $xml->startDocument('1.0', 'UTF-8');
        $xml->startElement('urlset');

        $xml->writeAttribute('xmlns:xsi', "http://www.w3.org/2001/XMLSchema-instance");
        $xml->writeAttribute('xmlns', "http://www.sitemaps.org/schemas/sitemap/0.9");
        $xml->writeAttribute('xsi:schemaLocation', "http://www.sitemaps.org/schemas/sitemap/0.9 http://www.sitemaps.org/schemas/sitemap/0.9/sitemap.xsd");

        $urlsResult = $this->getUrls();

        foreach ($urlsResult as $urlInfo) {
            $xml->startElement('url');
            $xml->startElement('loc');
            $xml->text($this->completeUrl($urlInfo['url']));
            $xml->endElement();
            $xml->startElement('lastmod');
            $xml->text(date('Y-m-d\TH:i:sP', $urlInfo['createtime']));
            $xml->endElement();
            $xml->startElement('changefreq');
            $xml->text("daily");
            $xml->endElement();
            $xml->startElement('priority');
            $xml->text("8.0");
            $xml->endElement();
            $xml->endElement();
        }
        $xml->endElement();
        $xml->endDocument();
        $xml = $xml->outputMemory();

        file_put_contents('./sitemap.xml', $xml);

        echo("SiteMap create success:" . count($urlsResult) . " date: " . date('Y-m-d H:i:s') . PHP_EOL . "<br>");
        return true;
    }

    public function getUrls()
    {
        $urlList = Db::name('sys_sitemap_url')->select()->toArray();
        return empty($urlList) ? [] : $urlList;
    }

    private function completeUrl($url)
    {
        $hostUrl = $this->site;

        $t = strtolower($url);
        if ((substr($t, 0, 7) == 'http://') || (substr($t, 0, 8) == 'https://') || (substr($t, 0, 2) == '//')) {
            return $url;
        }

        return $hostUrl . "/" . trim($url, '/');
    }

    private function createRobots()
    {
        if (!is_file("./robots.txt")) {
            $txt = "User-agent: *\n";
            $txt .= "Disallow: /app/admin\n";
            $txt .= "Disallow: /install\n";
            $txt .= "Sitemap: {$this->site}/sitemap.xml\n";

            // 将内容写入robots.txt文件
            $file = fopen("robots.txt", "w");
            fwrite($file, $txt);
            fclose($file);
        }
    }

    private function httpPost($api, $urls)
    {
        $ch = curl_init();
        $options = [
            CURLOPT_URL            => $api,
            CURLOPT_POST           => true,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POSTFIELDS     => implode("\n", $urls),
            CURLOPT_HTTPHEADER     => ['Content-Type: text/plain'],
        ];
        curl_setopt_array($ch, $options);
        $result = curl_exec($ch);
        return $result;
    }
}