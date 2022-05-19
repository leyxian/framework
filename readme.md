> 基于thinkphp，实现对数据库表的增删改查操作

> 对于只需要实现表的基本接口请求操作，又不想重复写控制器，可使用此类

### 下载安装
composer require echodao\framework

# 功能一
### 引入方法
加入中间件 edao\middleware\Table

### 请求说明
requrest header或者param 加入-table参数 直接实现表的 index、save、read、update、delete 接口操作

默认路由 表名（虽然可以任意）/操作名

### 权限说明
可以在权限认证中间件检测 -table 参数实现对于权限的识别判断，请将权限认证中间件至于本类中间件之前。

# 功能二
### 根据模型字段定义修改表，参数参考 think-migrate
在模型实现 getTableSchema 方法

```php
public function getTableSchema()
{
    return [
        'name' => 'test',
        'columns' => [
            ['name'=> 'test', 'type'=>'string', 'options'=>['limit'=>50]],
            ['name'=> 'create_time', 'type'=>'integer', 'options'=>['null'=> true, 'comment'=>'添加时间']],
        ]
    ];
}
```

执行命令 php think migrate:model --model app\\model\\test 实现表的创建与修改

创建表时请在模型加入代码，否则会报错
```php
protected $autoWriteTimestamp = false;
```