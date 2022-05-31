<?php

namespace edao\think;

use edao\command\Migrate;
use think\Service;

class Library extends Service
{
    public function boot()
    {
        $this->commands([Migrate::class]);
    }

    public function register()
    {
        $this->app->lang->load(dirname(dirname(__FILE__)).'/lang/zh-cn.php');
    }
}