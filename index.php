<!DOCTYPE html>
<html lang="zh-CN">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>API 接口文档</title>
    <style>
        body { 
            font-family: Arial, sans-serif; 
            max-width: 1200px; 
            margin: 0 auto; 
            padding: 20px; 
            background-color: #f5f5f5; 
        } 
        .api-card { 
            background-color: white; 
            border-radius: 8px; 
            padding: 20px; 
            margin-bottom: 20px; 
            box-shadow: 0 2px 4px rgba(0,0,0,0.1); 
            transition: transform 0.2s; 
        } 
        .api-card:hover { 
            transform: translateY(-5px); 
        } 
        .api-title { 
            color: #2c3e50; 
            margin-top: 0; 
        } 
        .api-description { 
            color: #7f8c8d; 
            margin-bottom: 15px; 
        } 
        .api-method { 
            display: inline-block; 
            padding: 4px 8px; 
            border-radius: 4px; 
            color: white; 
            font-weight: bold; 
            margin-right: 10px; 
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
        } 
        .btn-detail { 
            display: inline-block; 
            padding: 8px 16px; 
            background-color: #3498db; 
            color: white; 
            text-decoration: none; 
            border-radius: 4px; 
            margin-top: 10px; 
        } 
        .btn-detail:hover { 
            background-color: #2980b9; 
        }
    </style>
</head>
<body>
    <h1>API 接口文档</h1>
    <div id="api-container"></div>

    <script>
        // 定义API文件所在目录
        const apiDir = 'apich';

        // 自动扫描并加载所有 "api+数字.json" 格式的文件
        async function loadAPIs() {
            const allAPIs = [];
            const container = document.getElementById('api-container');
            
            try {
                // 1. 先获取目录下的所有文件（需要后端支持，这里用前端模拟的方式，实际可通过后端接口返回文件列表）
                // 注：如果是本地环境（如XAMPP），前端无法直接扫描目录，因此这里改用“按数字递增尝试加载”的方式
                let apiNum = 1;
                while (true) {
                    const filePath = `${apiDir}/api${apiNum}.json`;
                    try {
                        const response = await fetch(filePath);
                        if (!response.ok) throw new Error('文件不存在');
                        
                        const data = await response.json();
                        // 合并api_list数据
                        if (data.api_list && Array.isArray(data.api_list)) {
                            allAPIs.push(...data.api_list);
                        }
                        apiNum++;
                    } catch (err) {
                        // 当文件不存在时停止尝试
                        break;
                    }
                }
                
                // 2. 如果没有加载到任何API，提示用户
                if (allAPIs.length === 0) {
                    container.innerHTML = '<p style="color: red;">未加载到任何API数据，请检查文件路径和格式。</p>';
                    return;
                }
                
                // 3. 渲染API列表
                renderAPIs(allAPIs);
            } catch (err) {
                container.innerHTML = `<p style="color: red;">加载API失败：${err.message}</p>`;
            }
        }

        // 渲染 API 列表
        function renderAPIs(apis) {
            const container = document.getElementById('api-container');
            container.innerHTML = ''; // 清空容器
            
            apis.forEach(api => {
                const card = document.createElement('div');
                card.className = 'api-card';
                
                // 兼容name/title字段
                const apiName = api.name || api.title;
                
                card.innerHTML = `
                    <h2 class="api-title">${apiName}</h2>
                    <p class="api-description">${api.description}</p>
                    <span class="api-method ${api.method.toLowerCase()}">${api.method.toUpperCase()}</span>
                    <span class="api-endpoint">${api.endpoint}</span>
                    <a href="apixp.php?id=${api.id}" class="btn-detail">查看详情</a>
                `;
                
                container.appendChild(card);
            });
        }

        // 页面加载完成后执行
        document.addEventListener('DOMContentLoaded', loadAPIs);
    </script>
</body>
</html>
