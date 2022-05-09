<?php
namespace edao\controller;

use think\facade\Request;
use think\facade\Db;
use think\facade\Validate;

class Base {

    private $_table = '';

    public function __construct()
    {
        $this->_table = Request::header('-table') ?? Request::param('-table');
    }

    public function index()
    {
        $get = Request::except(['-table', 'page', 'limit'], 'get');
        $pageSize = Request::get('limit');
        $scheam = $this->getTableScheamInfo();
        $where = [];
        foreach($get as $k => $g){
            if(in_array($k, $scheam['fields'])){
                if($scheam['type'][$k]=='string')
                    $where[] = [$k, 'like', '%'.$g.'%'];
                else
                    $where[] = [$k, '=', $g];
            }
        }
        $list = Db::name($this->_table)->where($where)->order('id desc')->paginate($pageSize);

        return json(['code'=>1, 'msg'=>'查询成功', 'data'=>$list->toArray()]);
    }

    public function save()
    {
        $post = Request::except(['-table'], 'post');

        $createTime = $createTimeType = $updateTime = $updateTimeType = '';
        $scheam = $this->getTableScheamInfo();
        $times = ['create_time', 'create_at'];
        foreach($times as $t){
            if(in_array($t, $scheam['fields'])){
                $createTime = $t;
                $createTimeType = $scheam['type'][$updateTime];
            }
        }        
        $times = ['update_time', 'update_at'];
        foreach($times as $t){
            if(in_array($t, $scheam['fields'])){
                $updateTime = $t;
                $updateTimeType = $scheam['type'][$updateTime];
            }
        }
        if($createTime)
            $post[$createTime] = $createTimeType == 'int' ? time() : date('Y-m-d H:i:s');
        if($updateTime)
            $post[$updateTime] = $updateTimeType == 'int' ? time() : date('Y-m-d H:i:s');
        
        $id = Db::name($this->_table)->insertGetId($post);
        return json($id ? ['code'=>1, 'msg'=>'添加成功', 'data'=>['id'=>$id]] : ['code'=>0, 'msg'=>'添加失败']);
    }

    public function read()
    {
        $id = Request::get('id');
        $data = Db::name($this->table)->find($id);
        return json(['code'=>1, 'msg'=>'查询成功', 'data'=>$data]);
    }

    public function update()
    {
        $post = Request::except(['-table'], 'post');

        $scheam = $this->getTableScheamInfo();

        $validate = Validate::rule([
            $scheam['pk'] => 'required'
        ]);
        if(!$validate->check($post))
            return json(['code'=>0, 'msg'=>$validate->getError()]);

        $id = Request::get('id');

        $times = ['update_time', 'update_at'];
        $updateTime = ''; $updateTimeType = '';
        foreach($times as $t){
            if(in_array($t, $scheam['fields'])){
                $updateTime = $t;
                $updateTimeType = $scheam['type'][$updateTime];
            }
        }
        if($updateTime)
            $post[$updateTime] = $updateTimeType == 'int' ? time() : date('Y-m-d H:i:s');

        $result = Db::name($this->_table)->save($post);
        return json($result ? ['code'=>1, 'msg'=>'更新成功'] : ['code'=>0, 'msg'=>'更新失败']);
    }   

    public function delete()
    {
        $id = Request::post('id');
        $force = Request::post('force', '');

        $scheam = $this->getTableScheamInfo();

        $validate = Validate::rule([
            $scheam['pk'] => 'required'
        ]);
        if(!$validate->check(Request::post()))
            return json(['code'=>0, 'msg'=>$validate->getError()]);

        $scheam = $this->getTableScheamInfo();
        $deleteTime = ''; $deleteTimeType = '';
        if(!$force){
            if(in_array('delete_time', $scheam['fields'])){
                $deleteTime = 'delete_time';
                $deleteTimeType = $scheam['fields']['type'][$deleteTime];
            }
        }
        if($deleteTime){
            $result = Db::name($this->_table)
                ->where('id', $id)
                ->useSoftDelete($deleteTime, $deleteTimeType=='int' ? time() : date("Y-m-d H:i:s"))
                ->delete($id);
        }else{
            $result = Db::name($this->_table)
                ->where('id', $id)
                ->delete($id);
        }
        return json($result ? ['code'=>1, 'msg'=>'删除成功'] : ['code'=>0, 'msg'=>'删除失败']);
    }

    public function test()
    {
        return json($this->getTableScheamInfo());
    }

    private function getTableScheamInfo()
    {
        $model = Db::name($this->_table);
        return Db::connect()->getSchemaInfo($model->getTable());
    }
}