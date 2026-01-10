<?php
// 获取API ID
$apiId = isset($_GET['id']) ? intval($_GET['id']) : 0;
if ($apiId <= 0) {
    die("无效的API ID");
}

// 定义API文件所在目录
$apiDir = 'apich';
$allApiData = [];

// 检查目录是否存在
if (!is_dir($apiDir)) {
    die("API文件目录不存在");
}

// 1. 扫描目录，筛选出所有 "api+数字.json" 格式的文件
$files = scandir($apiDir);
$apiFiles = [];
foreach ($files as $file) {
    // 用正则匹配文件名：必须是 api开头 + 数字 + .json 结尾
    if (preg_match('/^api(\d+)\.json$/', $file, $matches)) {
        $apiNum = intval($matches[1]); // 提取文件名里的数字（比如api1.json提取1）
        $apiFiles[$apiNum] = $apiDir . '/' . $file; // 按数字作为键存储文件路径
    }
}

// 2. 按数字从小到大排序（确保api1在api2前面，api10在api9后面）
ksort($apiFiles);

// 3. 按顺序读取所有匹配的文件，合并api_list数据
foreach ($apiFiles as $filePath) {
    if (file_exists($filePath)) {
        $jsonData = file_get_contents($filePath);
        $apiData = json_decode($jsonData, true);
        // 合并合法的api_list数据
        if ($apiData && isset($apiData['api_list']) && is_array($apiData['api_list'])) {
            $allApiData = array_merge($allApiData, $apiData['api_list']);
        }
    }
}

// 检查是否读取到API数据
if (empty($allApiData)) {
    die("未读取到任何API配置数据");
}

// 查找对应的API
$targetApi = null;
foreach ($allApiData as $api) {
    if ($api['id'] == $apiId) {
        $targetApi = $api;
        break;
    }
}

if (!$targetApi) {
    die("未找到ID为{$apiId}的API");
}
?>
<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo htmlspecialchars($targetApi['name']); ?> - API详情</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            max-width: 1000px;
            margin: 0 auto;
            padding: 20px;
            background-color: #f5f5f5;
        }
        .api-detail-container {
            background-color: white;
            border-radius: 8px;
            padding: 30px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .api-header {
            margin-bottom: 30px;
        }
        .api-title {
            color: #2c3e50;
            margin-top: 0;
        }
        .api-description {
            color: #7f8c8d;
            font-size: 16px;
        }
        .api-method-endpoint {
            display: flex;
            align-items: center;
            gap: 10px;
            margin-top: 10px;
        }
        .api-method {
            display: inline-block;
            padding: 6px 12px;
            border-radius: 4px;
            color: white;
            font-weight: bold;
            font-size: 14px;
        }
        .api-method.get {
            background-color: #3498db;
        }
        .api-method.post {
            background-color: #2ecc71;
        }
        .api-method.put {
            background-color: #f39c12;
        }
        .api-method.delete {
            background-color: #e74c3c;
        }
        .api-endpoint {
            color: #34495e;
            font-family: monospace;
            font-size: 18px;
            flex: 1;
        }
        .btn-copy {
            padding: 6px 12px;
            background-color: #3498db;
            color: white;
            border: none;
            border-radius: 4px;
            cursor: pointer;
            font-size: 14px;
        }
        .btn-copy:hover {
            background-color: #2980b9;
        }
        .btn-copy:active {
            transform: scale(0.98);
        }
        .section-title {
            color: #2c3e50;
            border-bottom: 1px solid #eee;
            padding-bottom: 10px;
            margin-top: 30px;
        }
        .parameters-table {
            width: 100%;
            border-collapse: collapse;
            margin-top: 15px;
        }
        .parameters-table th, .parameters-table td {
            border: 1px solid #eee;
            padding: 10px;
            text-align: left;
        }
        .parameters-table th {
            background-color: #f9f9f9;
            color: #2c3e50;
        }
        .required {
            color: #e74c3c;
            font-weight: bold;
        }
        .response-example {
            background-color: #f9f9f9;
            padding: 15px;
            border-radius: 4px;
            font-family: monospace;
            overflow-x: auto;
            margin-top: 15px;
            white-space: pre-wrap;
            text-align: center;
        }
        /* 给图片和视频统一样式 */
        .response-example img,
        .response-example video {
            max-width: 100%;
            max-height: 400px;
            border-radius: 4px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        .btn-back {
            display: inline-block;
            padding: 8px 16px;
            background-color: #95a5a6;
            color: white;
            text-decoration: none;
            border-radius: 4px;
            margin-top: 20px;
        }
        .btn-back:hover {
            background-color: #7f8c8d;
        }
    </style>
</head>
<body>
    <div class="api-detail-container">
        <div class="api-header">
            <h1 class="api-title"><?php echo htmlspecialchars($targetApi['name']); ?></h1>
            <p class="api-description"><?php echo htmlspecialchars($targetApi['description']); ?></p>
            <div class="api-method-endpoint">
                <div class="api-method <?php echo strtolower($targetApi['method']); ?>">
                    <?php echo $targetApi['method']; ?>
                </div>
                <span class="api-endpoint"><?php echo htmlspecialchars($targetApi['endpoint']); ?></span>
                <button class="btn-copy" onclick="copyToClipboard('<?php echo $targetApi['endpoint']; ?>')">复制</button>
            </div>
        </div>

        <div class="api-section">
            <h2 class="section-title">请求参数</h2>
            <?php if (!empty($targetApi['parameters'])): ?>
                <table class="parameters-table">
                    <thead>
                        <tr>
                            <th>参数名</th>
                            <th>类型</th>
                            <th>是否必填</th>
                            <th>描述</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($targetApi['parameters'] as $param): ?>
                            <tr>
                                <td><?php echo htmlspecialchars($param['param_name']); ?></td>
                                <td><?php echo htmlspecialchars($param['param_type']); ?></td>
                                <td><?php echo $param['is_required'] ? '<span class="required">是</span>' : '否'; ?></td>
                                <td><?php echo htmlspecialchars($param['description']); ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p>该接口无需请求参数。</p>
            <?php endif; ?>
        </div>

        <div class="api-section">
            <h2 class="section-title">返回示例</h2>
            <div class="response-example">
                <?php 
                // 1. 先判断是否是视频链接
                if (is_string($targetApi['response_example']) && 
                    (strpos($targetApi['response_example'], 'http') === 0) &&
                    preg_match('/\.(mp4|webm|mov|avi|mkv)$/i', $targetApi['response_example'])) {
                    // 渲染视频控件（带播放按钮）
                    echo '<video src="' . htmlspecialchars($targetApi['response_example']) . '" controls alt="返回示例视频"></video>';
                }
                // 2. 再判断是否是图片链接
                elseif (is_string($targetApi['response_example']) && 
                    (strpos($targetApi['response_example'], 'http') === 0) &&
                    preg_match('/\.(png|jpg|jpeg|gif|webp)$/i', $targetApi['response_example'])) {
                    // 渲染图片
                    echo '<img src="' . htmlspecialchars($targetApi['response_example']) . '" alt="返回示例图片">';
                }
                // 3. 其他情况（JSON/文字）
                else {
                    echo htmlspecialchars(json_encode($targetApi['response_example'], JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
                }
                ?>
            </div>
        </div>

        <a href="index.php" class="btn-back">返回API列表</a>
    </div>

    <script>
        function copyToClipboard(text) {
            navigator.clipboard.writeText(text).then(function() {
                // 找到复制按钮
                const btn = document.querySelector('.btn-copy');
                const originalText = btn.textContent;
                
                // 显示复制成功的提示
                btn.textContent = '已复制!';
                btn.style.backgroundColor = '#2ecc71';
                
                // 2秒后恢复按钮状态
                setTimeout(function() {
                    btn.textContent = originalText;
                    btn.style.backgroundColor = '#3498db';
                }, 2000);
            }).catch(function(err) {
                console.error('复制失败:', err);
                alert('复制失败，请手动复制');
            });
        }
    </script>
</body>
</html>
