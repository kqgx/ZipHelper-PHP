# ZipHelper - PHP文件压缩下载工具

ZipHelper是一个强大而安全的PHP类，用于将多个文件或目录压缩并提供下载功能。经过安全增强和错误处理优化，适合在生产环境中使用。

## 功能特点

- 支持单个或多个文件的压缩
- 支持整个目录的递归压缩（包括子目录）
- 支持混合内容（文件和目录）的压缩
- 支持自定义压缩包中的文件路径
- 支持直接下载或保存到服务器
- 链式调用API，使用方便
- 完整的错误处理和异常机制
- 安全的文件名处理，防止路径遍历攻击
- 自动检查文件和目录权限
- 资源自动清理，防止内存泄漏
- 自动创建不存在的临时目录和保存目录

## 系统要求

- PHP 5.6 或更高版本
- ZipArchive扩展（必需）
- 文件读写权限

## 安装方法

1. 将`ZipHelper.php`文件复制到您的项目中
2. 在需要使用的PHP文件中引入该类：

```php
require_once 'ZipHelper.php';
```

3. 确保PHP环境已启用ZipArchive扩展

## 使用示例

### 1. 压缩并下载单个文件

```php
try {
    $zipHelper = new ZipHelper('单个文件.zip');
    $zipHelper->addFile('test_files/documents/readme.txt')
        ->download();
} catch (\Exception $e) {
    echo "下载单个文件失败: " . $e->getMessage();
}
```

### 2. 压缩并下载多个文件

```php
try {
    $zipHelper = new ZipHelper('多个文件.zip');
    $zipHelper->addFile('test_files/documents/readme.txt')
        ->addFile('test_files/code/sample.php', 'php/sample.php')  // 自定义在压缩包中的路径
        ->addFile('test_files/code/config.json', 'config/config.json')
        ->download();
} catch (\Exception $e) {
    echo "下载多个文件失败: " . $e->getMessage();
}
```

### 3. 压缩并下载整个目录

```php
try {
    $zipHelper = new ZipHelper('代码目录.zip');
    $zipHelper->addDirectory('test_files/code')
        ->download();
} catch (\Exception $e) {
    echo "下载目录失败: " . $e->getMessage();
}
```

### 4. 压缩混合内容（文件和目录）

```php
try {
    $zipHelper = new ZipHelper('混合内容.zip');
    $zipHelper->addFile('test_files/config.ini')
        ->addDirectory('test_files/images')
        ->addFile('test_files/documents/readme.txt', 'docs/自述文件.txt')  // 自定义在压缩包中的路径
        ->addDirectory('test_files/code', 'src')     // 自定义在压缩包中的路径
        ->download();
} catch (\Exception $e) {
    echo "下载混合内容失败: " . $e->getMessage();
}
```

### 5. 保存压缩包到服务器而不是下载

```php
try {
    $zipHelper = new ZipHelper('服务器存档.zip');
    $zipHelper->addDirectory('test_files')
        ->saveTo('.');  // 保存到当前目录
    
    echo "压缩包已保存到服务器。路径：" . getcwd() . DIRECTORY_SEPARATOR . '服务器存档.zip';
} catch (\Exception $e) {
    echo "保存压缩包到服务器失败: " . $e->getMessage();
}
```

## API参考

### 构造函数

```php
public function __construct($zipName = 'archive.zip', $tempPath = null)
```

- `$zipName` - 压缩包文件名，默认为'archive.zip'
- `$tempPath` - 临时文件存储路径，默认为系统临时目录
- 会检查ZipArchive扩展是否可用
- 如果临时目录不存在，会尝试自动创建
- 会检查临时目录是否可写

### 方法

#### addFile

```php
public function addFile($file, $localName = null)
```

添加文件到压缩列表:
- `$file` - 要添加的文件路径
- `$localName` - 在压缩包中的路径（可选），默认使用原文件名
- 返回 ZipHelper 实例，支持链式调用
- 会检查文件是否存在和可读

#### addDirectory

```php
public function addDirectory($directory, $localName = null)
```

添加目录到压缩列表:
- `$directory` - 要添加的目录路径
- `$localName` - 在压缩包中的路径（可选），默认使用原目录名
- 返回 ZipHelper 实例，支持链式调用
- 会检查目录是否存在和可读

#### create

```php
public function create()
```

创建压缩包:
- 返回生成的压缩包临时文件路径
- 会检查源列表是否为空
- 自动处理路径分隔符，确保兼容性
- 会检查压缩操作是否成功

#### download

```php
public function download($deleteAfterDownload = true)
```

下载压缩包:
- `$deleteAfterDownload` - 是否在下载后删除临时文件，默认为true
- 自动设置正确的HTTP头，包括文件类型和缓存控制
- 会检查读取操作是否成功
- 注意：该方法会调用exit()，终止脚本执行

#### saveTo

```php
public function saveTo($savePath)
```

保存压缩包到指定路径:
- `$savePath` - 保存路径，如果不存在会自动创建
- 返回保存的文件完整路径
- 会检查保存路径是否可写，不可写时会抛出异常
- 会检查文件复制操作是否成功

#### clear

```php
public function clear()
```

清空压缩列表:
- 返回 ZipHelper 实例，支持链式调用
- 用于重用同一个ZipHelper实例创建多个压缩包

## 安全注意事项

- 确保对用户输入的文件路径进行验证和过滤
- 避免将敏感文件添加到可公开下载的压缩包中
- 确保Web服务器对临时目录和保存目录有适当的写入权限
- 对于大型目录压缩，请考虑内存和执行时间限制

## 错误处理

ZipHelper类使用PHP异常机制处理错误。所有操作都有详细的错误检查和异常抛出，您应该使用try-catch块包装对ZipHelper的调用，以捕获和处理可能的异常。

```php
try {
    // 使用ZipHelper的代码
} catch (\InvalidArgumentException $e) {
    // 处理参数错误
    echo "参数错误: " . $e->getMessage();
} catch (\RuntimeException $e) {
    // 处理运行时错误
    echo "运行时错误: " . $e->getMessage();
} catch (\Exception $e) {
    // 处理其他错误
    echo "发生错误: " . $e->getMessage();
}
```

## 许可证

MIT 