<?php

namespace edao\controller;

use think\service\ModelService;

trait Controller {

    protected $model;

    public function index()
    {
        $query = $this->beforeAction();
        $data = $query->paginate();
        $data = $this->afterAction($data);
        if($this->request->isAjax())
            return json(['data'=>$data]);
        $this->assign('data', $data);
        $this->fetch();
    }

    public function create()
    {
        # code...
    }

    public function save()
    {
        $data = $this->beforeAction();
        if(!$data) $data = input('post.');
        if($this->model->save($data)){
            $pk = $this->model->getPk();
            return json(['info'=>lang('save_success'), 'code'=>1, 'data'=>$this->model->$pk]);
        }else
            return json(['info'=>lang('save_error'), 'code'=>0]);
    }

    public function read()
    {   
        $id = input('id');
        $data = $this->model->find($id);
        return json(['data'=>$data]);
    }

    public function edit()
    {
        $id = input('id');
        $model = $this->model->get($id);
        if(!$model) return  json(['info'=>lang('data_not_found'), 'code'=>0]);
    }

    public function update()
    {
        # code...
    }

    public function delete()
    {
        # code...
    }

    abstract public function getModel();

    /**
     * Undocumented function
     *
     * @return void
     */
    private function beforeAction() {
        $query = $this->getModel()->db();
        $action = $this->request->action();
        $beforeAction = '_before_'.$action;
        if(method_exists($this, $beforeAction))
            return $this->$beforeAction($query);
        return false;
    }

    /**
     * Undocumented function
     *
     * @return void
     */
    private function afterAction(&$data) {
        $action = $this->request->action();
        $afterAction = '_after_'.$action;
        if(method_exists($this, $afterAction))
            return $afterAction($data);
        return false;
    }
}