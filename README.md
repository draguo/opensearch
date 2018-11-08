<div style="text-align:center">
  <h1>阿里云 opensearch</h1>
</div>

## 配置
```php
$config = [
    'app_id' => '', // 应用id
    'secret' => '', // 应用密钥
    'app_name' => '', // 应用名
    'suggest_name' => '', // 搜索名
    'host' => 'http://opensearch-cn-beijing.aliyuncs.com', // 主机地址
];
$app = new Opensearch($config);
```
## 下拉提示
```php
// String $query 搜索词
// int $hit 限制返回数量
$result = $search->suggest($query, $hit);
```