<?php
/**
 * 文件预览通用服务
 * 封装与业务模型无关的文件预览通用逻辑：文件类型识别、文本内容获取、PDF内容代理、下载URL生成等
 * 任何应用均可复用，调用方只需传入文件 URL、扩展名等基础信息，无需传入具体业务模型
 * @author fengzi
 */
namespace app\admin\service;

use think\facade\Config;
use think\facade\Log;

class FilePreviewService
{
    /**
     * 文件类型映射（扩展名 -> 类型分组）
     * 默认从 config/file_preview.php 读取，调用方可通过 setFileTypes() 覆盖
     * @var array
     */
    protected $fileTypes = [];

    /**
     * 支持文本预览的扩展名白名单
     * 仅这些扩展名的文件才会被 getTextContent() 处理，防止任意文件被读取
     * @var array
     */
    protected $textPreviewExts = [];

    /**
     * 文本预览临时文件下载超时时间（秒）
     * @var int
     */
    protected $textDownloadTimeout = 60;

    /**
     * PDF 内容代理下载超时时间（秒）
     * @var int
     */
    protected $pdfDownloadTimeout = 30;

    /**
     * 构造函数：从 config/file_preview.php 读取默认配置
     * 各应用可通过 setFileTypes() / setTextPreviewExts() 覆盖
     * @author fengzi
     */
    public function __construct()
    {
        $config = Config::get('file_preview', []);
        if (!empty($config['file_types'])) {
            $this->fileTypes = $config['file_types'];
        }
        if (!empty($config['text_preview_exts'])) {
            $this->textPreviewExts = $config['text_preview_exts'];
        }
        if (isset($config['text_download_timeout'])) {
            $this->textDownloadTimeout = (int)$config['text_download_timeout'];
        }
        if (isset($config['pdf_download_timeout'])) {
            $this->pdfDownloadTimeout = (int)$config['pdf_download_timeout'];
        }
    }

    /**
     * 设置文件类型映射（覆盖默认配置）
     * @param array $fileTypes 文件类型配置
     * @return $this
     * @author fengzi
     */
    public function setFileTypes(array $fileTypes): self
    {
        $this->fileTypes = $fileTypes;
        return $this;
    }

    /**
     * 设置支持文本预览的扩展名白名单
     * @param array $exts 扩展名数组
     * @return $this
     * @author fengzi
     */
    public function setTextPreviewExts(array $exts): self
    {
        $this->textPreviewExts = $exts;
        return $this;
    }

    /**
     * 检测文件类型分组
     * @param string $ext 文件扩展名（不区分大小写）
     * @return string|null image|document|video|audio；未匹配返回 null
     * @author fengzi
     */
    public function detectFileType(string $ext): ?string
    {
        $ext = strtolower($ext);
        foreach ($this->fileTypes as $type => $config) {
            if (in_array($ext, $config['extensions'] ?? [], true)) {
                return $type;
            }
        }
        return null;
    }

    /**
     * 判断是否支持在线预览
     * @param string $ext 文件扩展名
     * @return bool
     * @author fengzi
     */
    public function isPreviewSupported(string $ext): bool
    {
        $ext = strtolower($ext);
        foreach ($this->fileTypes as $config) {
            if (in_array($ext, $config['extensions'] ?? [], true)) {
                return in_array($ext, $config['preview_support'] ?? [], true);
            }
        }
        return false;
    }

    /**
     * 获取文件图标
     * @param string $ext 文件扩展名
     * @return string
     * @author fengzi
     */
    public function getFileIcon(string $ext): string
    {
        $type = $this->detectFileType($ext);
        if ($type && isset($this->fileTypes[$type]['icon'])) {
            return $this->fileTypes[$type]['icon'];
        }
        return 'fa fa-file-o';
    }

    /**
     * 格式化文件大小
     * @param int $bytes 字节数
     * @return string
     * @author fengzi
     */
    public function formatFileSize($bytes): string
    {
        $bytes = (int)$bytes;
        if ($bytes >= 1073741824) {
            return number_format($bytes / 1073741824, 2) . ' GB';
        } elseif ($bytes >= 1048576) {
            return number_format($bytes / 1048576, 2) . ' MB';
        } elseif ($bytes >= 1024) {
            return number_format($bytes / 1024, 2) . ' KB';
        } elseif ($bytes > 1) {
            return $bytes . ' bytes';
        } elseif ($bytes == 1) {
            return $bytes . ' byte';
        }
        return '0 bytes';
    }

    /**
     * 获取下载URL（将相对路径转换为完整域名地址）
     * @param string $fileUrl 文件URL（相对路径）
     * @return string 完整URL
     * @author fengzi
     */
    public function getDownloadUrl(string $fileUrl): string
    {
        return tomedia($fileUrl);
    }

    /**
     * 获取文本文件内容
     * 将远程文件下载到本地临时目录，返回本地可访问的URL供前端 fetch 读取
     * 采用缓存机制：同一文件（按 $cacheKey + fileUrl md5）只下载一次
     * @param string $fileUrl 文件URL（相对路径）
     * @param string $ext 文件扩展名
     * @param int    $uniacid 商户ID（用于临时目录隔离）
     * @param string $cacheKey 缓存键（建议传入业务ID，如素材ID，确保唯一）
     * @return array ['local_url' => string, 'file_ext' => string]
     * @throws \Exception
     * @author fengzi
     */
    public function getTextContent(string $fileUrl, string $ext, int $uniacid, string $cacheKey): array
    {
        $fileExt = strtolower($ext);

        // 扩展名白名单校验，防止任意文件被读取
        if (!in_array($fileExt, $this->textPreviewExts, true)) {
            throw new \Exception('不支持的文件格式');
        }

        if (empty($fileUrl)) {
            throw new \Exception('文件地址不能为空');
        }

        // 缓存键白名单校验，防止路径穿越攻击（仅允许字母、数字、下划线、连字符）
        if (!preg_match('/^[a-zA-Z0-9_\-]+$/', $cacheKey)) {
            throw new \Exception('缓存键包含非法字符');
        }

        // 临时目录按商户隔离，避免跨商户文件混淆
        $attachmentPath = IA_ROOT . '/public/attachment/';
        $tempDir = $attachmentPath . 'temp/preview/' . $uniacid . '/';

        if (!is_dir($tempDir)) {
            if (!mkdir($tempDir, 0755, true)) {
                throw new \Exception('临时目录创建失败');
            }
        }

        // 文件名包含缓存键和 fileUrl 的 md5，确保同一文件只下载一次
        $tempFilename = 'preview_' . $cacheKey . '_' . md5($fileUrl) . '.' . $fileExt;
        $tempFilePath = $tempDir . $tempFilename;
        $tempFileUrl = 'temp/preview/' . $uniacid . '/' . $tempFilename;

        // 缓存命中：本地已存在则直接返回
        if (!file_exists($tempFilePath)) {
            $fullUrl = tomedia($fileUrl);
            $content = $this->downloadFile($fullUrl, $this->textDownloadTimeout);

            if (file_put_contents($tempFilePath, $content) === false) {
                throw new \Exception('文件保存失败');
            }
        }

        $localUrl = request()->domain() . '/attachment/' . $tempFileUrl;

        return [
            'local_url' => $localUrl,
            'file_ext'  => $fileExt,
        ];
    }

    /**
     * 获取PDF文件内容（base64编码）
     * 用于绕过OSS强制下载头，让前端 PDF.js 可直接渲染
     * @param string $fileUrl 文件URL（相对路径）
     * @return array ['content' => string(base64), 'size' => int]
     * @throws \Exception
     * @author fengzi
     */
    public function getPdfContent(string $fileUrl): array
    {
        if (empty($fileUrl)) {
            throw new \Exception('文件地址不能为空');
        }

        $fullUrl = tomedia($fileUrl);
        $content = $this->downloadFile($fullUrl, $this->pdfDownloadTimeout);

        return [
            'content' => base64_encode($content),
            'size'    => strlen($content),
        ];
    }

    /**
     * 通过 curl 下载远程文件内容
     * @param string $url 完整URL
     * @param int    $timeout 超时时间（秒）
     * @return string 文件内容
     * @throws \Exception
     * @author fengzi
     */
    protected function downloadFile(string $url, int $timeout): string
    {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        curl_setopt($ch, CURLOPT_SSL_VERIFYHOST, false);
        curl_setopt($ch, CURLOPT_TIMEOUT, $timeout);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 5);

        $content = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);

        if ($content === false || $httpCode !== 200 || $error) {
            Log::error('FilePreviewService 文件下载失败', [
                'url'       => $url,
                'http_code' => $httpCode,
                'error'     => $error,
            ]);
            throw new \Exception('文件下载失败: ' . ($error ?: 'HTTP ' . $httpCode));
        }

        return $content;
    }
}
