<?php

/**
 * ZipHelper - 文件和目录压缩下载工具类
 */
class ZipHelper
{
    /**
     * 源文件或目录
     * @var array
     */
    private $sources = [];

    /**
     * 压缩包文件名
     * @var string
     */
    private $zipName = 'archive.zip';

    /**
     * 临时文件存储路径
     * @var string
     */
    private $tempPath = '';

    /**
     * 构造函数
     * 
     * @param string $zipName 压缩包文件名
     * @param string|null $tempPath 临时目录路径，如果不存在会自动创建
     * @throws \RuntimeException 如果创建临时目录失败或ZipArchive扩展未启用
     */
    public function __construct($zipName = 'archive.zip', $tempPath = null)
    {
        // 检查ZipArchive扩展是否可用
        if (!class_exists('ZipArchive')) {
            throw new \RuntimeException('ZipArchive扩展未启用，请在PHP配置中启用该扩展。');
        }

        $this->zipName = $this->sanitizeFilename($zipName);
        $this->tempPath = $tempPath ?: sys_get_temp_dir();

        // 确保临时目录存在
        if (!is_dir($this->tempPath)) {
            // 尝试创建临时目录
            if (!@mkdir($this->tempPath, 0755, true)) {
                throw new \RuntimeException("临时目录不存在，且无法创建: {$this->tempPath}");
            }
        }

        // 确保临时目录可写
        if (!is_writable($this->tempPath)) {
            throw new \RuntimeException("临时目录不可写: {$this->tempPath}");
        }
    }

    /**
     * 净化文件名，防止路径遍历和特殊字符
     * 
     * @param string $filename 文件名
     * @return string 净化后的文件名
     */
    private function sanitizeFilename($filename)
    {
        // 移除路径信息，只保留文件名
        $filename = basename($filename);

        // 确保文件名不为空
        if (empty($filename)) {
            return 'archive.zip';
        }

        // 确保文件扩展名为.zip
        if (strtolower(pathinfo($filename, PATHINFO_EXTENSION)) !== 'zip') {
            $filename .= '.zip';
        }

        return $filename;
    }

    /**
     * 添加文件到压缩列表
     * 
     * @param string $file 文件路径
     * @param string|null $localName 在压缩包中的路径
     * @return ZipHelper
     */
    public function addFile($file, $localName = null)
    {
        if (!file_exists($file)) {
            throw new \InvalidArgumentException("文件不存在: {$file}");
        }

        if (!is_readable($file)) {
            throw new \InvalidArgumentException("文件不可读: {$file}");
        }

        $this->sources[] = [
            'type' => 'file',
            'path' => $file,
            'local_name' => $localName
        ];

        return $this;
    }

    /**
     * 添加目录到压缩列表
     * 
     * @param string $directory 目录路径
     * @param string|null $localName 在压缩包中的路径
     * @return ZipHelper
     */
    public function addDirectory($directory, $localName = null)
    {
        if (!is_dir($directory)) {
            throw new \InvalidArgumentException("目录不存在: {$directory}");
        }

        if (!is_readable($directory)) {
            throw new \InvalidArgumentException("目录不可读: {$directory}");
        }

        $this->sources[] = [
            'type' => 'directory',
            'path' => $directory,
            'local_name' => $localName
        ];

        return $this;
    }

    /**
     * 创建压缩包
     * 
     * @return string 生成的压缩包临时文件路径
     * @throws \RuntimeException 如果创建压缩包失败
     */
    public function create()
    {
        // 检查是否有文件或目录要压缩
        if (empty($this->sources)) {
            throw new \InvalidArgumentException("没有添加任何文件或目录到压缩列表");
        }

        $zipPath = rtrim($this->tempPath, '/\\') . DIRECTORY_SEPARATOR . $this->zipName;

        $zip = new \ZipArchive();
        if ($zip->open($zipPath, \ZipArchive::CREATE | \ZipArchive::OVERWRITE) !== true) {
            throw new \RuntimeException("无法创建压缩包: {$zipPath}");
        }

        try {
            foreach ($this->sources as $source) {
                if ($source['type'] === 'file') {
                    $localName = $source['local_name'] ?: basename($source['path']);
                    // 确保本地路径不包含前导斜杠
                    $localName = ltrim($localName, '/\\');
                    if (!$zip->addFile($source['path'], $localName)) {
                        throw new \RuntimeException("无法添加文件到压缩包: {$source['path']}");
                    }
                } else if ($source['type'] === 'directory') {
                    $this->addDirectoryToZip($zip, $source['path'], $source['local_name']);
                }
            }

            if (!$zip->close()) {
                throw new \RuntimeException("无法完成压缩包创建: {$zipPath}");
            }
        } catch (\Exception $e) {
            // 关闭压缩包并删除临时文件
            $zip->close();
            if (file_exists($zipPath)) {
                @unlink($zipPath);
            }
            throw $e;
        }

        return $zipPath;
    }

    /**
     * 递归添加目录内容到压缩包
     * 
     * @param \ZipArchive $zip 压缩包对象
     * @param string $directory 目录路径
     * @param string|null $localPath 在压缩包中的路径
     * @throws \RuntimeException 如果添加目录到压缩包失败
     */
    private function addDirectoryToZip($zip, $directory, $localPath = null)
    {
        $dir = @opendir($directory);
        if ($dir === false) {
            throw new \RuntimeException("无法打开目录: {$directory}");
        }

        $localPath = $localPath ?: basename($directory);

        // 确保目录路径以斜杠结尾，并且不包含前导斜杠
        $localPath = ltrim(rtrim($localPath, '/\\') . '/', '/\\');

        // 创建目录
        if (!$zip->addEmptyDir($localPath)) {
            closedir($dir);
            throw new \RuntimeException("无法在压缩包中创建目录: {$localPath}");
        }

        while (($file = readdir($dir)) !== false) {
            // 跳过. 和..
            if ($file === '.' || $file === '..') {
                continue;
            }

            $filePath = $directory . DIRECTORY_SEPARATOR . $file;
            $localFilePath = $localPath . $file;

            if (is_dir($filePath)) {
                $this->addDirectoryToZip($zip, $filePath, $localFilePath);
            } else if (is_readable($filePath)) {
                if (!$zip->addFile($filePath, $localFilePath)) {
                    closedir($dir);
                    throw new \RuntimeException("无法添加文件到压缩包: {$filePath}");
                }
            }
        }

        closedir($dir);
    }

    /**
     * 下载压缩包
     * 
     * @param boolean $deleteAfterDownload 下载后是否删除临时文件
     * @throws \RuntimeException 如果下载压缩包失败
     */
    public function download($deleteAfterDownload = true)
    {
        $zipPath = $this->create();

        if (!file_exists($zipPath)) {
            throw new \RuntimeException("压缩包文件不存在: {$zipPath}");
        }

        // 确保没有之前的输出
        if (ob_get_level()) {
            ob_end_clean();
        }

        // 设置HTTP头
        header('Content-Type: application/zip');
        header('Content-Disposition: attachment; filename="' . $this->zipName . '"');
        header('Content-Length: ' . filesize($zipPath));
        header('Cache-Control: no-cache, must-revalidate');
        header('Pragma: no-cache');
        header('Expires: 0');

        // 读取文件并输出
        if (readfile($zipPath) === false) {
            throw new \RuntimeException("无法读取压缩包文件: {$zipPath}");
        }

        // 如果需要，删除临时文件
        if ($deleteAfterDownload && file_exists($zipPath)) {
            @unlink($zipPath);
        }

        exit;
    }

    /**
     * 保存压缩包到指定路径
     * 
     * @param string $savePath 保存路径，如果不存在会自动创建
     * @return string 保存的文件路径
     * @throws \RuntimeException 如果保存压缩包失败
     */
    public function saveTo($savePath)
    {
        // 确保保存路径存在
        if (!is_dir($savePath)) {
            // 尝试创建保存目录
            if (!@mkdir($savePath, 0755, true)) {
                throw new \RuntimeException("保存路径不存在，且无法创建: {$savePath}");
            }
        }

        // 确保保存路径可写
        if (!is_writable($savePath)) {
            throw new \RuntimeException("保存路径不可写: {$savePath}");
        }

        $zipPath = $this->create();
        $saveFilePath = rtrim($savePath, '/\\') . DIRECTORY_SEPARATOR . $this->zipName;

        if (!copy($zipPath, $saveFilePath)) {
            throw new \RuntimeException("无法保存压缩包到: {$saveFilePath}");
        }

        // 删除临时文件
        if (file_exists($zipPath)) {
            @unlink($zipPath);
        }

        return $saveFilePath;
    }

    /**
     * 清空压缩列表
     * 
     * @return ZipHelper
     */
    public function clear()
    {
        $this->sources = [];
        return $this;
    }
}
