
# 下载安装
composer require echodao\framework-tool

# 功能一
## 根据模型注释修改表结构，参数参考 think-migrate
在模型加入注释
```php
/**
 * Demo Class
 * @var string connection
 * @var string table demo 
 * @var string options {"id":false,"primary_key":["demo_id","user_id"],"engine":"MyISAM","collation":"utf8_general_ci"}
 * @var string columns {"name":"demo_id","type":"integer"}
 * @var string columns {"name":"user_id","type":"integer"}
 * @var string columns {"name":"name","type":"string","options":{"limit":50,"comment":"标题"}}
 * @var string columns {"name":"status","type":"boolean","options":{"limit":1},"comment":"状态"}
 * @var string columns {"name":"create_time","type":"integer","options":{"null":true,"comment":"添加时间"}}
 * @var string indexes
 * @var string foreignkeys {}
 */
```

执行命令 php think migrate:model 更新所有模型表
* 参数 --model app\\\model\\\Demo 指定模型更新表
* 参数 --conn app\\\model\\\Demo 指定数据库连接

# 技术支持
**QQ群：** 67761412