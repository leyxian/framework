<?php

namespace edao\command;

use Exception;
use Phinx\Db\Adapter\AdapterFactory;
use Phinx\Db\Table;
use Reflection;
use think\console\Command;
use think\console\Input;
use think\console\input\Argument;
use think\console\input\Option;
use think\console\Output;
use think\facade\Db;
use ReflectionMethod;
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
     * protected $autoWriteTimestamp = false;
     */
    protected function execute(Input $input, Output $output)
    {
        $output->writeln('执行开始');
        try{
            if(!$input->hasOption('model')) throw new Exception('--model need');
            $connection = $input->hasOption('conn') ? $input->getOption('conn') : '';
            if($input->hasOption('model')){
                $model = invoke($input->getOption('model'));
                $tableName = $model->db()->getTable();
                $tableSchema = $model->getTableSchema();
                if(!$connection) $connection = $model->getConnection();
            }
            $options = $this->getDbConfig($connection);
            $adapter = AdapterFactory::instance()->getAdapter($options['adapter'] ?? '', $options);
            $table = new Table($tableName, $tableSchema['options']??[], $adapter);
            $scheamInfo = $table->exists() ? Db::connect($connection)->getSchemaInfo($tableName, true) : [];
            // var_dump($scheamInfo); die;
            foreach($tableSchema['columns'] as $col){
                if(!$table->exists() || !$table->hasColumn($col['column']))
                    $table->addColumn($col['column'], $col['type'], $col['options']??[]);
                else
                    $table->changeColumn($col['column'], $col['type'], $col['options']??[]);
                if($scheamInfo){
                    foreach($scheamInfo['fields'] as $ke => $field){
                        if($col['column'] == $field )
                            unset($scheamInfo['fields'][$ke]);
                    }
                }
            }
            if(isset($tableSchema['indexes']) && $tableSchema['indexes']){
                foreach($tableSchema['indexes'] as $index){
                    if(!$table->exists() || !$table->hasIndex($index['name']))
                        $table->addIndex($index['columns'], $index['options']??[]);
                }
            }
            if(isset($tableSchema['foreignkeys']) && $tableSchema['foreignkeys']){
                foreach($tableSchema['foreignkeys'] as $index){
                    if(!$table->exists() || !$table->hasIndex($index['name']))
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
        }catch( \Exception $e ){
            $output->writeln('执行失败：'.$e->getMessage());
            $output->writeln($e->getTraceAsString());            
        }
        $output->writeln('执行完成');
    }

    protected function getDbConfig(string $connection=''): array
    {
        $default = $connection ?: config('database.default');
        $config = config("database.connections.{$default}");
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

        $table = $this->app->config->get('database.migration_table', 'migrations');

        $dbConfig['default_migration_table'] = $dbConfig['table_prefix'] . $table;

        return $dbConfig;
    }
}