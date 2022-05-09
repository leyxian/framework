<?php

namespace edao\middleware;

use edao\controller\Base;
use Reflection;
use ReflectionMethod;
use think\facade\Request;

class Table
{
    public function handle($request, \Closure $next)
    {
        if($request->param('-table') || $request->header('-table')){
            $action = $request->action();
            try{
                $method = new ReflectionMethod('edao\controller\Base', $action);
                $instance = new Base;
                if($method->isPublic())
                    return app('edao\controller\Base')->$action();
            }catch( \ReflectionException $e ){
                
            }
        }
        return $next($request);
    }
}