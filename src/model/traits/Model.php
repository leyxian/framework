<?php

namespace edao\model\traits;

trait Model
{
    public function connect(string $conn)
    {
        $this->connection = $conn;
    }
}


// $config = Config::get('database.connections.mysql');
// $options = [
//     'adapter'      => $config['type'],
//     'host'         => $config['hostname'],
//     'name'         => $config['database'],
//     'user'         => $config['username'],
//     'pass'         => $config['password'],
//     'port'         => $config['hostport'],
//     'charset'      => $config['charset'],
//     'table_prefix' => $config['prefix'],
// ];
// $adapter = AdapterFactory::instance()->getAdapter($options['adapter'] ?? '', $options);
// if ($adapter->hasOption('table_prefix') || $adapter->hasOption('table_suffix')) {
//     $adapter = AdapterFactory::instance()->getWrapper('prefix', $adapter);
// }

// $table = new Table('test', [], $adapter);
// $res = $table->addColumn('name', 'string')
//     ->addColumn('price', 'decimal')
//     ->addColumn('status', 'boolean', ['limit'=>1])
//     ->addTimestamps()
//     ->addSoftDelete()
//     ->create();

// return $res;


