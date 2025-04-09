<?php
// 引入ZipHelper类
require_once 'ZipHelper.php';

/**
 * 测试环境检查
 * 确保所有必要的条件都满足
 */
function checkEnvironment()
{
    // 检查 ZipArchive 扩展
    if (!class_exists('ZipArchive')) {
        die("错误: ZipArchive 扩展未启用，请在PHP配置中启用该扩展。");
    }

    // 检查测试文件是否存在
    $requiredFiles = [
        'test_files/documents/readme.txt',
        'test_files/code/sample.php',
        'test_files/code/config.json',
        'test_files/images/image_info.txt',
        'test_files/config.ini'
    ];

    $missingFiles = [];
    foreach ($requiredFiles as $file) {
        if (!file_exists($file)) {
            $missingFiles[] = $file;
        }
    }

    if (!empty($missingFiles)) {
        die("错误: 以下测试文件不存在:\n" . implode("\n", $missingFiles));
    }

    // 检查测试目录是否存在
    $requiredDirs = [
        'test_files',
        'test_files/documents',
        'test_files/code',
        'test_files/images'
    ];

    $missingDirs = [];
    foreach ($requiredDirs as $dir) {
        if (!is_dir($dir)) {
            $missingDirs[] = $dir;
        }
    }

    if (!empty($missingDirs)) {
        die("错误: 以下测试目录不存在:\n" . implode("\n", $missingDirs));
    }

    echo "环境检查通过，所有必要条件都满足。\n";
}

// 示例1：压缩并下载单个文件
function downloadSingleFile()
{
    try {
        $zipHelper = new ZipHelper('单个文件.zip');
        $zipHelper->addFile('test_files/documents/readme.txt')
            ->download();
        // 由于download()方法会调用exit，所以这里不会执行
        echo "压缩包已生成并开始下载。";
    } catch (\Exception $e) {
        echo "下载单个文件失败: " . $e->getMessage();
    }
}

// 示例2：压缩并下载多个文件
function downloadMultipleFiles()
{
    try {
        $zipHelper = new ZipHelper('多个文件.zip');
        $zipHelper->addFile('test_files/documents/readme.txt')
            ->addFile('test_files/code/sample.php', 'php/sample.php')  // 自定义在压缩包中的路径
            ->addFile('test_files/code/config.json', 'config/config.json')
            ->download();
        // 由于download()方法会调用exit，所以这里不会执行
        echo "压缩包已生成并开始下载。";
    } catch (\Exception $e) {
        echo "下载多个文件失败: " . $e->getMessage();
    }
}

// 示例3：压缩并下载整个目录
function downloadDirectory()
{
    try {
        $zipHelper = new ZipHelper('代码目录.zip');
        $zipHelper->addDirectory('test_files/code')
            ->download();
        // 由于download()方法会调用exit，所以这里不会执行
        echo "压缩包已生成并开始下载。";
    } catch (\Exception $e) {
        echo "下载目录失败: " . $e->getMessage();
    }
}

// 示例4：压缩混合内容（文件和目录）
function downloadMixed()
{
    try {
        $zipHelper = new ZipHelper('混合内容.zip');
        $zipHelper->addFile('test_files/config.ini')
            ->addDirectory('test_files/images')
            ->addFile('test_files/documents/readme.txt', 'docs/自述文件.txt')  // 自定义在压缩包中的路径
            ->addDirectory('test_files/code', 'src')     // 自定义在压缩包中的路径
            ->download();
        // 由于download()方法会调用exit，所以这里不会执行
        echo "压缩包已生成并开始下载。";
    } catch (\Exception $e) {
        echo "下载混合内容失败: " . $e->getMessage();
    }
}

// 示例5：保存压缩包到服务器而不是下载
function saveZipToServer()
{
    try {
        $zipHelper = new ZipHelper('服务器存档.zip');
        $zipHelper->addDirectory('test_files')
            ->saveTo('.');  // 保存到当前目录

        echo "压缩包已保存到服务器。路径：" . getcwd() . DIRECTORY_SEPARATOR . '服务器存档.zip';
    } catch (\Exception $e) {
        echo "保存压缩包到服务器失败: " . $e->getMessage();
    }
}

// 检查环境
checkEnvironment();

// 根据需要调用以下任一函数
try {
    // 取消注释你想要执行的函数
    // downloadSingleFile();
    // downloadMultipleFiles();
    // downloadDirectory();
    // downloadMixed();
    // saveZipToServer();
} catch (\Exception $e) {
    echo "发生错误: " . $e->getMessage();
}
