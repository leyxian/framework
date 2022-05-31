<?php

namespace edao\command;

use Exception;
use Phinx\Db\Adapter\AdapterFactory;
use Phinx\Db\Table;
use ReflectionClass;
use think\console\Command;
use think\console\Input;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;
use think\facade\Log;

class Migrate extends Command
{
    protected function configure()
    {
        $this->setName('migrate:model')
            ->addOption('model', null, Option::VALUE_OPTIONAL, '模型路径')
            ->addOption('conn', null, Option::VALUE_OPTIONAL, '数据库配置名称')
        	->setDescription('根据模型修改表结构');
    }

    /**
     * 创建表示请设置
     */
    protected function execute(Input $input, Output $output)
    {
        $output->writeln('执行开始');
        try{
            $connection = $input->hasOption('conn') ? $input->getOption('conn') : '';
            if($input->hasOption('model')){
                $this->makeModel($input->getOption('model'), $connection);
            }else{
                $files = $this->scanModelDirectory(base_path());
                foreach($files as $file){
                    $name = substr($file, strlen(strtr(root_path(), '\\', '/')) - 1);
                    if (preg_match("|^([\w/]+)/(\w+)/model/(.+)\.php$|i", $name, $mats)) {
                        [, $namespace, $appname, $classname] = $mats;
                        $output->writeln(strtr("{$namespace}/{$appname}/model/{$classname}", '/', '\\'));
                        $this->makeModel(strtr("{$namespace}/{$appname}/model/{$classname}", '/', '\\'), $connection);
                    }
                }
            }
        }catch( \Exception $e ){
            $output->writeln('执行失败：'.$e->getMessage());
            $output->writeln($e->getTraceAsString());
        }
        $output->writeln('执行完成');
    }

    protected function makeModel(string $model, string $connection=''){
        $output = new Output;
        $tableModel = new ReflectionClass($model);
        if(preg_match_all('/@var\s+string\s+(connection|table|options|columns|indexes|foreignkeys)([^\n]+)?/', $tableModel->getDocComment(), $mats)){
            foreach($mats[1] as $k => $mat){
                $row = trim($mats[2][$k]);
                if($row && $row!='{}'){
                    if(in_array($mat, ['columns', 'indexes', 'foreignkeys']))
                        $tableSchema[$mat][] = json_decode($row, true) ?? $row;
                    else
                        $tableSchema[$mat] = json_decode($row, true) ?? $row;
                }                        
            }
            if(!$connection) $connection = $tableSchema['connection'] ?? '';
            $tableName = $tableSchema['table'];
            $options = $this->getDbConfig($connection);
            $adapter = AdapterFactory::instance()->getAdapter($options['adapter'] ?? '', $options);
            $table = new Table($tableName, $tableSchema['options']??[], $adapter);
            $scheamInfo = $table->exists() ? Db::connect($connection)->getSchemaInfo($tableName, true) : [];
            foreach($tableSchema['columns'] as $col){
                if(!$table->exists() || !$table->hasColumn($col['name']))
                    $table->addColumn($col['name'], $col['type'], $col['options']??[]);
                else
                    $table->changeColumn($col['name'], $col['type'], $col['options']??[]);
                if($scheamInfo){
                    foreach($scheamInfo['fields'] as $ke => $field){
                        if($col['name'] == $field)
                            unset($scheamInfo['fields'][$ke]);
                    }
                }
            }
            if(isset($tableSchema['indexes']) && $tableSchema['indexes']){
                foreach($tableSchema['indexes'] as $index){
                    if(!$table->exists() || !$table->hasIndex($index['columns']))
                        $table->addIndex($index['columns'], $index['options']??[]);
                }
            }
            if(isset($tableSchema['foreignkeys']) && $tableSchema['foreignkeys']){
                foreach($tableSchema['foreignkeys'] as $index){
                    if(!$table->exists() || !$table->hasIndex($index['columns']))
                        $table->addForeignKey($index['columns'], $index['referencedTable'], $index['referencedColumns']??['id'], $index['options'] ??[]);
                }
            }
            if($scheamInfo && $scheamInfo['fields']){
                $pk = is_array($scheamInfo['pk']) ?? [$scheamInfo['pk']];
                foreach($scheamInfo['fields'] as $field) {
                    if(in_array($field, $pk)) continue;
                    $table->removeColumn($field);
                    Log::record('conn: '.$connection.' table: '.$tableName.' column: '.$field.' remove '.json_encode($scheamInfo['type'][$field]));
                }
            }
            if($adapter->hasTable($tableName)){
                $table->update();
                $output->writeln('表格 '.$tableName.' 更新成功');
            }else{
                $table->create();
                $output->writeln('表格 '.$tableName.' 添加成功');
            }
        }else{
            $output->writeln('模型 '.$model.' 无注释内容');
        }
    }

    protected function getDbConfig(string $connection=''): array
    {
        $default = $connection ?: config('database.default');
        $config = config('database.connections.'.$default);
        if (0 == $config['deploy']) {
            $dbConfig = [
                'adapter'      => $config['type'],
                'host'         => $config['hostname'],
                'name'         => $config['database'],
                'user'         => $config['username'],
                'pass'         => $config['password'],
                'port'         => $config['hostport'],
                'charset'      => $config['charset'],
                'table_prefix' => $config['prefix'],
            ];
        } else {
            $dbConfig = [
                'adapter'      => explode(',', $config['type'])[0],
                'host'         => explode(',', $config['hostname'])[0],
                'name'         => explode(',', $config['database'])[0],
                'user'         => explode(',', $config['username'])[0],
                'pass'         => explode(',', $config['password'])[0],
                'port'         => explode(',', $config['hostport'])[0],
                'charset'      => explode(',', $config['charset'])[0],
                'table_prefix' => explode(',', $config['prefix'])[0],
            ];
        }
        return $dbConfig;
    }

    public function scanModelDirectory(string $path, array $data = [], ?string $ext = 'php'): array
    {
        if (file_exists($path)) if (is_file($path) && strpos($path, 'model'.DIRECTORY_SEPARATOR)!==false) {
            $data[] = strtr($path, '\\', '/');
        } elseif (is_dir($path)) foreach (scandir($path) as $item) if ($item[0] !== '.') {
            $real = rtrim($path, '\\/') . DIRECTORY_SEPARATOR . $item;
            if (is_readable($real)) if (is_dir($real)) {
                $data = $this->scanModelDirectory($real, $data, $ext);
            } elseif (is_file($real) && (is_null($ext) || pathinfo($real, 4) === $ext) && strpos($real, 'model'.DIRECTORY_SEPARATOR)!==false) {
                $data[] = strtr($real, '\\', '/');
            }
        }
        return $data;
    }
}