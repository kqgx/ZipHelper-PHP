这是一个示例文本文件，用于测试ZipHelper类的压缩功能。

该文件将被添加到压缩包中，您可以通过以下方式使用ZipHelper类添加此文件：

$zipHelper = new ZipHelper('my_archive.zip');
$zipHelper->addFile('test_files/documents/readme.txt');

您还可以指定此文件在压缩包中的路径：

$zipHelper->addFile('test_files/documents/readme.txt', 'docs/readme.txt');

祝您使用愉快！ 