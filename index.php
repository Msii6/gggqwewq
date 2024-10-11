<?php
require 'vendor/autoload.php';  // 引入 Google API 客户端

use Google\Cloud\Translate\V2\TranslateClient;

function translateText($text, $targetLanguage) {
    // 使用Google Translate API
    $translate = new TranslateClient([
        'key' => 'YOUR_GOOGLE_API_KEY', // 替换为你的API密钥
    ]);

    $translation = $translate->translate($text, [
        'target' => $targetLanguage,
    ]);

    return $translation['text'];
}

function translateSrtFile($inputFile, $outputFile, $targetLanguage) {
    $fileContent = file_get_contents($inputFile);
    $lines = explode("\n", $fileContent);

    $translatedLines = [];
    foreach ($lines as $line) {
        if (trim($line) && !preg_match('/^\d+$/', $line) && !strpos($line, '-->')) {
            // 翻译非时间戳和编号的行
            $translatedLine = translateText($line, $targetLanguage);
            $translatedLines[] = $translatedLine;
        } else {
            $translatedLines[] = $line; // 不翻译时间戳和编号
        }
    }

    // 将翻译结果写入新文件
    file_put_contents($outputFile, implode("\n", $translatedLines));
}

// 处理文件上传和翻译
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['subtitle'])) {
    $targetLanguage = $_POST['language'];  // 用户选择的目标语言
    $uploadedFile = $_FILES['subtitle']['tmp_name'];
    $outputFile = 'translated_' . $_FILES['subtitle']['name'];

    // 调用翻译函数
    translateSrtFile($uploadedFile, $outputFile, $targetLanguage);

    // 提供下载链接
    echo "文件翻译成功！<a href='$outputFile'>下载翻译后的字幕文件</a>";
}
?>

<!-- HTML表单 -->
<form action="" method="POST" enctype="multipart/form-data">
    <label for="subtitle">上传字幕文件 (.srt):</label>
    <input type="file" name="subtitle" id="subtitle" required><br>

    <label for="language">选择目标语言:</label>
    <select name="language" id="language" required>
        <option value="en">英文</option>
        <option value="zh-CN">中文</option>
        <option value="es">西班牙语</option>
        <!-- 添加更多语言选项 -->
    </select><br>

    <button type="submit">翻译字幕</button>
</form>
