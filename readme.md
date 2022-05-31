
# 下载安装
composer require echodao\framework-tool

# 功能一
> 基于thinkphp，实现对数据库表的增删改查操作

> 对于只需要实现表的基本接口请求操作，又不想重复写控制器，可使用此类
## 引入方法
加入中间件 edao\middleware\Table

## 请求说明
requrest header或者param 加入-table参数 直接实现表的 index、save、read、update、delete 接口操作

默认路由 表名（虽然可以任意）/操作名

## 权限说明
可以在权限认证中间件检测 -table 参数实现对于权限的识别判断，请将权限认证中间件至于本类中间件之前。

# 功能二
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