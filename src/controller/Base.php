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
        return json($this->getTableScheamInfo());
        $get = Request::except(['-table', 'page', 'limit'], 'get');
        $pageSize = Request::get('limit');
        $list = Db::name($this->_table)->where($get)->order('id desc')->paginate($pageSize);

        return json(['code'=>1, 'msg'=>'查询成功', 'data'=>$list->toArray()]);
    }

    public function save()
    {
        $post = Request::except(['-table'], 'post');
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

    private function getTableScheamInfo()
    {
        $model = Db::name($this->_table);
        return Db::connect()->getSchemaInfo($model->getTable());
    }
}