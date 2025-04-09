<?php
/**
 * 示例PHP文件
 * 用于测试ZipHelper类的压缩功能
 */

// 一个简单的函数
function sayHello($name) {
    return "你好，{$name}！";
}

// 一个简单的类
class SimpleExample {
    private $message;
    
    public function __construct($message = '这是默认消息') {
        $this->message = $message;
    }
    
    public function getMessage() {
        return $this->message;
    }
    
    public function setMessage($message) {
        $this->message = $message;
        return $this;
    }
}

// 使用示例
$example = new SimpleExample();
echo $example->getMessage() . "\n";
echo sayHello('世界');
?> 