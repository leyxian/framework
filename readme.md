> 基于thinkphp，实现对数据库表的增删改查操作

> 对于只需要实现表的基本接口请求操作，又不想重复写控制器，可使用此类

### 下载安装
composer require echodao\framework

### 引入方法
加入中间件 edao\middleware\Table

### 请求说明
requrest header或者param 加入-table参数 直接实现表的 index、save、read、update、delete 接口操作

默认路由 表名（虽然可以任意）/操作名

### 权限说明
可以在权限认证中间件检测 -table 参数实现对于权限的识别判断，请将权限认证中间件至于本类中间件之前。