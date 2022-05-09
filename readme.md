> 基于thinkphp，实现对数据库表的增删改查操作

> 对于只需要实现表的基本接口请求操作，可使用此类

### 下载安装
composer require echodao\framework

### 引入方法
加入中间件 edao\middleware\Table

### 请求说明
requrest header或者param 加入-table参数 直接实现表的 index、save、read、update、delete 接口操作

默认路由 表名/操作名