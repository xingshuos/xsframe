<?php
/**
 * 文件上传通用服务
 * 封装文件类型配置整理、上传文件校验和上传结果校验，供各应用上传控制器复用。
 * 文件存储、OSS 路径和业务权限仍由具体应用控制器负责。
 * @author fengzi
 */
namespace app\admin\service;

use think\Exception;

class FileUploadService
{
    /**
     * 组装前端上传组件使用的文件类型配置。
     * @param array $fileTypes 文件类型配置
     * @param array $fileSizeLimits 文件大小限制，单位为 MB
     * @return array
     * @author fengzi
     */
    public function buildFileTypesConfig(array $fileTypes, array $fileSizeLimits = []): array
    {
        $normalized = $this->normalizeFileTypesConfig($fileTypes, $fileSizeLimits);

        return [
            'file_types'         => $fileTypes,
            'type_groups'        => $normalized['type_groups'],
            'allowed_extensions' => $normalized['allowed_extensions'],
            'file_size_limits'   => $normalized['file_size_limits'],
            'extension_icons'    => $normalized['extension_icons'],
        ];
    }

    /**
     * 统一整理前端配置和后端校验共同使用的文件规则。
     *
     * @param array $fileTypes 文件类型配置
     * @param array $fileSizeLimits 文件大小限制，单位为 MB
     * @return array
     * @author fengzi
     */
    public function normalizeFileTypesConfig(array $fileTypes, array $fileSizeLimits = []): array
    {
        $allowedExtensions = [];
        $typeGroups = [];
        $extensionIcons = [];

        foreach ($fileTypes as $type => $config) {
            $type = (string)$type;
            $extensions = [];
            foreach ((array)($config['extensions'] ?? []) as $extension) {
                $extension = strtolower(trim((string)$extension));
                if ($extension === '') {
                    continue;
                }
                $extensions[] = $extension;
                $allowedExtensions[] = $extension;
                $extensionIcons[$extension] = (string)($config['icon'] ?? 'fa fa-file-o');
            }

            $previewSupport = [];
            foreach ((array)($config['preview_support'] ?? []) as $extension) {
                $extension = strtolower(trim((string)$extension));
                if ($extension !== '') {
                    $previewSupport[] = $extension;
                }
            }

            $typeGroups[$type] = [
                'name'            => (string)($config['name'] ?? $type),
                'extensions'      => array_values(array_unique($extensions)),
                'icon'            => (string)($config['icon'] ?? 'fa fa-file-o'),
                'preview_support' => array_values(array_unique($previewSupport)),
            ];
        }

        return [
            'type_groups'        => $typeGroups,
            'allowed_extensions' => array_values(array_unique($allowedExtensions)),
            'file_size_limits'   => $this->normalizeFileSizeLimits($fileSizeLimits),
            'extension_icons'    => $extensionIcons,
        ];
    }

    /**
     * 校验上传文件的扩展名和大小。
     * @param mixed $file 上传文件对象
     * @param array $fileTypes 文件类型配置
     * @param array $fileSizeLimits 文件大小限制，单位为 MB
     * @return array 扩展名、大小和 MIME 类型
     * @throws Exception
     * @author fengzi
     */
    public function validateFile($file, array $fileTypes, array $fileSizeLimits = []): array
    {
        if (empty($file) || !method_exists($file, 'extension') || !method_exists($file, 'getSize') || !method_exists($file, 'getMime')) {
            throw new Exception('请选择上传文件');
        }

        $extension = strtolower(trim((string)$file->extension()));
        $fileSize = (int)$file->getSize();
        $normalized = $this->normalizeFileTypesConfig($fileTypes, $fileSizeLimits);
        $matchedType = $this->validateExtensionAndSize(
            $extension,
            $fileSize,
            $normalized,
            '文件大小超过限制，最大支持'
        );

        return [
            'file_ext'  => $extension,
            'file_size' => $fileSize,
            'mime_type' => (string)($file->getMime() ?: 'application/octet-stream'),
            'type'      => $matchedType,
        ];
    }

    /**
     * 校验已上传文件结果中的扩展名和大小。
     *
     * 添加、编辑和版本提交不能只依赖前端隐藏字段，因此提交落库前仍使用与上传接口相同的规则复核。
     * @param array $data 已上传文件结果
     * @param array $fileTypes 文件类型配置
     * @param array $fileSizeLimits 文件大小限制，单位为 MB
     * @return array 扩展名、大小和类型
     * @throws Exception
     * @author fengzi
     */
    public function validateUploadedData(array $data, array $fileTypes, array $fileSizeLimits = []): array
    {
        $fileUrl = $data['file_url'] ?? '';
        if ($fileUrl === null || $fileUrl === '') {
            throw new Exception('请先上传文件');
        }
        if (!is_string($fileUrl) && !is_numeric($fileUrl)) {
            throw new Exception('文件信息无效，请重新上传文件');
        }
        if (trim((string)$fileUrl) === '') {
            throw new Exception('请先上传文件');
        }

        $fileExtension = $data['file_ext'] ?? '';
        if (!is_string($fileExtension) && !is_numeric($fileExtension)) {
            throw new Exception('文件信息无效，请重新上传文件');
        }
        $extension = strtolower(trim((string)$fileExtension));

        $fileSizeValue = $data['file_size'] ?? 0;
        if (!is_string($fileSizeValue) && !is_numeric($fileSizeValue)) {
            throw new Exception('文件信息无效，请重新上传文件');
        }
        $fileSize = (int)$fileSizeValue;
        $normalized = $this->normalizeFileTypesConfig($fileTypes, $fileSizeLimits);
        $matchedType = $this->validateExtensionAndSize(
            $extension,
            $fileSize,
            $normalized,
            '文件大小超过限制，最大支持'
        );

        return [
            'file_ext'  => $extension,
            'file_size' => $fileSize,
            'type'      => $matchedType,
        ];
    }

    /**
     * 按统一配置校验扩展名和文件大小。
     * @param string $extension 文件扩展名
     * @param int $fileSize 文件大小，单位为字节
     * @param array $normalizedConfig 标准化文件配置
     * @param string $sizeMessage 文件超限提示前缀
     * @return string 匹配到的文件类型
     * @throws Exception
     * @author fengzi
     */
    private function validateExtensionAndSize(
        string $extension,
        int $fileSize,
        array $normalizedConfig,
        string $sizeMessage
    ): string {
        $allowedExtensions = $normalizedConfig['allowed_extensions'] ?? [];
        $typeGroups = $normalizedConfig['type_groups'] ?? [];
        $fileSizeLimits = $normalizedConfig['file_size_limits'] ?? ['default' => 50];

        if ($extension === '' || !in_array($extension, $allowedExtensions, true)) {
            throw new Exception('不支持的文件格式');
        }
        if ($fileSize < 0) {
            throw new Exception('文件大小无效，请重新上传文件');
        }

        $matchedType = '';
        foreach ($typeGroups as $type => $config) {
            if (in_array($extension, $config['extensions'] ?? [], true)) {
                $matchedType = (string)$type;
                break;
            }
        }
        if ($matchedType === '') {
            throw new Exception('不支持的文件格式');
        }

        $limitMb = $this->resolveFileSizeLimit($matchedType, $fileSizeLimits);
        $limitBytes = (int)($limitMb * 1024 * 1024);
        if ($fileSize > $limitBytes) {
            throw new Exception($sizeMessage . $limitMb . 'MB');
        }

        return $matchedType;
    }

    /**
     * 规范化文件大小限制，确保前端展示和后端计算使用相同的正数 MB 值。
     * @param array $fileSizeLimits 原始文件大小限制
     * @return array 标准化后的文件大小限制
     * @author fengzi
     */
    private function normalizeFileSizeLimits(array $fileSizeLimits): array
    {
        $normalized = [];
        foreach ($fileSizeLimits as $type => $limit) {
            if (!is_numeric($limit) || (float)$limit <= 0) {
                continue;
            }
            $limit = (float)$limit;
            $normalized[(string)$type] = floor($limit) === $limit ? (int)$limit : $limit;
        }

        if (!isset($normalized['default'])) {
            $normalized['default'] = 50;
        }

        return $normalized;
    }

    /**
     * 获取指定文件类型的大小限制。
     * @param string $type 文件类型
     * @param array $fileSizeLimits 标准化后的文件大小限制
     * @return float 大小限制，单位为 MB
     * @author fengzi
     */
    private function resolveFileSizeLimit(string $type, array $fileSizeLimits): float
    {
        $limit = $fileSizeLimits[$type] ?? $fileSizeLimits['default'] ?? 50;
        return (float)$limit;
    }

    /**
     * 检查上传成功结果是否包含公共配置声明的字段。
     * @param array $data 上传结果
     * @return bool
     * @author fengzi
     */
    public function isCompleteResult(array $data): bool
    {
        // app/admin/config 不在 ThinkPHP 默认扫描目录内，这里由后台公共服务显式读取。
        $requiredFields = $this->getResultRequiredFields();
        if (empty($requiredFields)) {
            return false;
        }

        foreach ($requiredFields as $field) {
            if (!array_key_exists($field, $data) || $data[$field] === null) {
                return false;
            }
        }

        return true;
    }

    /**
     * 读取公共上传结果必需字段配置。
     * @return array
     * @author fengzi
     */
    private function getResultRequiredFields(): array
    {
        static $requiredFields;

        if ($requiredFields !== null) {
            return $requiredFields;
        }

        $requiredFields = [];
        $configPath = dirname(__DIR__) . DIRECTORY_SEPARATOR . 'config' . DIRECTORY_SEPARATOR . 'file_upload.php';
        if (!is_file($configPath)) {
            return $requiredFields;
        }

        $config = include $configPath;
        if (is_array($config)) {
            $requiredFields = array_values(array_filter(
                array_map('strval', (array)($config['result_required_fields'] ?? [])),
                static function ($field) {
                    return $field !== '';
                }
            ));
        }

        return $requiredFields;
    }
}
