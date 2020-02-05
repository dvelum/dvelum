<?php /** @noinspection PhpMissingParentCallCommonInspection */
/** @noinspection PhpMissingParentCallCommonInspection */
/**
 *  DVelum project https://github.com/dvelum/dvelum
 *  Copyright (C) 2011-2017  Kirill Yegorov
 *
 *  This program is free software: you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation, either version 3 of the License, or
 *  (at your option) any later version.
 *
 *  This program is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
declare(strict_types=1);
namespace Dvelum\App\Backend\Orm\Controller;

use Dvelum\{
    App\Backend\Orm,
    Config,
    Db\Adapter,
    Request,
    Response
};

use Dvelum\Orm\{
    Model,
    Record\Manager,
    Record\Import
};


class Connections extends \Dvelum\App\Backend\Controller
{
    /**
     * @var Orm\Connections
     */
    protected $connections;

    public function getModule(): string
    {
        return 'Orm';
    }

    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);
        $this->connections = new Orm\Connections($this->appConfig->get('db_configs'));
    }

    public function indexAction()
    {
        $this->response->notFound();
    }

    public function listAction()
    {
        $devType = $this->request->post('devType', 'int', false);

        if($devType === false || !$this->connections->typeExists($devType)){
            $this->response->error($this->lang->get('WRONG_REQUEST') .' undefined devType');
            return;
        }


        $connections = $this->connections->getConnections($devType);
        $data = [];
        if(!empty($connections))
        {
            foreach ($connections as $name=>$cfg)
            {
                if($name === 'default')
                    $system = true;
                else
                    $system = false;

                $data[] = array(
                    'id' => $name ,
                    'system' => $system,
                    'devType' => $devType,
                    'username' => $cfg->get('username'),
                    'dbname' => $cfg->get('dbname'),
                    'host' => $cfg->get('host'),
                    'adapter'=> $cfg->get('adapter'),
                    'isolation'=> $cfg->get('transactionIsolationLevel')
                );
            }
        }
        $this->response->success($data);
    }

    public function removeAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $id = $this->request->post('id', 'string', false);

        if($id === false){
            $this->response->error($this->lang->get('WRONG_REQUEST') .' undefined id');
            return;
        }

        try{
            $this->connections->removeConnection($id);
        }
        catch (\Exception $e){
            $this->response->error($e->getMessage());
            return;
        }

        $this->response->success();
    }

    public function loadAction()
    {
        $id = $this->request->post('id', 'string', false);
        $devType = $this->request->post('devType', 'int', false);

        if($id === false){
            $this->response->error($this->lang->get('WRONG_REQUEST') .' undefined id');
            return;
        }


        if($devType === false || !$this->connections->typeExists($devType)){
            $this->response->error($this->lang->get('WRONG_REQUEST') .' undefined devType');
            return;
        }

        $data = $this->connections->getConnection($devType , $id);

        if(!$data){
            $this->response->error($this->lang->get('CANT_LOAD'));
            return;
        }


        $data = $data->__toArray();
        $data['id'] = $id;
        unset($data['password']);
        $this->response->success($data);
    }

    public function saveAction()
    {
        if(!$this->checkCanEdit()){
            return;
        }

        $oldId = $this->request->post('oldid', 'string', false);
        $id = $this->request->post('id', 'string', false);
        $devType = $this->request->post('devType', 'int', false);
        $host = $this->request->post('host', 'string', false);
        $user = $this->request->post('username', 'string', false);
        $base = $this->request->post('dbname', 'string', false);
        $charset = $this->request->post('charset', 'string', false);
        $pass = $this->request->post('password', 'string', false);

        $setpass = $this->request->post('setpass', 'boolean', false);
        $adapter = $this->request->post('adapter', 'string', false);
        $transactionIsolationLevel = $this->request->post('transactionIsolationLevel', 'string', false);
        $port = $this->request->post('port', 'int', false);
        $prefix = $this->request->post('prefix', 'string', '');

        if($devType === false){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }


        /*
         * INPUT FIX
         */
        if($oldId === 'false')
            $oldId = false;

        if($oldId === false || empty($oldId))
        {
            $cfg = $this->connections->getConfig();
            foreach ($cfg as $type=>$data){
                if($this->connections->connectionExists($type , $id)){
                    $this->response->error($this->lang->get('FILL_FORM') , array('id'=>$this->lang->get('SB_UNIQUE')));
                    return;
                }
            }

            if(!$this->connections->createConnection($id)){
                $this->response->error($this->lang->get('CANT_CREATE'));
                return;
            }

            $con = $this->connections->getConnection($devType, $id);
        }
        else
        {
            if($oldId!==$id)
            {
                $cfg = $this->connections->getConfig();
                foreach ($cfg as $type=>$data){
                    if($this->connections->connectionExists($type , $id)){
                        $this->response->error($this->lang->get('FILL_FORM') , array('id'=>$this->lang->get('SB_UNIQUE')));
                        return;
                    }
                }
            }

            if(!$this->connections->connectionExists($devType, $id) && $oldId===$id){
                $this->response->error($this->lang->get('WRONG_REQUEST'));
                return;
            }

            $con = $this->connections->getConnection($devType, (string) $oldId);
        }

        if(!$con){
            $this->response->error($this->lang->get('CANT_CREATE'));
            return;
        }


        if($setpass)
            $con->set('password', $pass);

        if($port!==false && $port!==0){
            $con->set('port', $port);
        }else{
            $con->remove('port');
        }

        $storage = Config::storage();

        // Disable config merging
        $con->setParentId(null);

        $con->set('username', $user);
        $con->set('dbname', $base);
        $con->set('host', $host);
        $con->set('charset', $charset);
        $con->set('adapter', $adapter);
        $con->set('driver', $adapter);
        $con->set('transactionIsolationLevel', $transactionIsolationLevel);
        $con->set('prefix' , $prefix);

        if(!$storage->save($con)){
            $this->response->error($this->lang->get('CANT_WRITE_FS') . ' ' . $con->getName());
            return;
        }

        if($oldId !==false && $oldId!==$id){
            if(!$this->connections->renameConnection($oldId, $id)){
                $this->response->error($this->lang->get('CANT_WRITE_FS'));
                return;
            }
        }
        $this->response->success();
    }

    public function testAction()
    {
        $id = $this->request->post('id', 'string', false);
        $devType = $this->request->post('devType', 'int', false);
        $port = $this->request->post('port', 'int', false);
        $host = $this->request->post('host', 'string', false);
        $user = $this->request->post('username', 'string', false);
        $base = $this->request->post('dbname', 'string', false);
        $charset = $this->request->post('charset', 'string', false);
        $pass = $this->request->post('password', 'string', false);
        $updatePwd = $this->request->post('setpass', 'boolean', false);
        $adapter = $this->request->post('adapter', 'string', false);
        $adapterNamespace = $this->request->post('adapterNamespace', 'string', false);

        if($devType === false){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }


        $config = array(
            'username' => $user,
            'password' => $pass,
            'dbname' => $base,
            'host' => $host,
            'charset' => $charset,
            'adapter' => $adapter,
            'adapterNamespace' => $adapterNamespace
        );

        if($port!==false)
            $config['port'] = $port;

        if($id!==false && $id!=='false' && !$updatePwd)
        {
            $oldCfg = $this->connections->getConnection($devType, $id);

            if(!$oldCfg){
                $this->response->error($this->lang->get('WRONG_REQUEST') .' invalid file');
                return;
            }
            $config['password']	 = $oldCfg->get('password');
        }

        try{
            $config['driver'] = $config['adapter'];
            $db = new Adapter($config);
            $db->query('SET NAMES ' . $charset);
            $this->response->success();
        }catch (\Exception $e){
            $this->response->error($this->lang->get('CANT_CONNECT').' ' . $e->getMessage());
        }
    }

    public function tableListAction()
    {
        $connectionId = $this->request->post('connId', 'string', false);
        $connectionType = $this->request->post('type', 'integer', false);

        if($connectionId === false || $connectionType===false){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $cfg = $this->connections->getConnection($connectionType, $connectionId);
        if(!$cfg){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }


        $cfg = $cfg->__toArray();

        $conManager = new \Dvelum\Db\Manager($this->appConfig);
        try{
            $connection = $conManager->initConnection($cfg);
        }catch (\Exception $e){
            $this->response->error($this->lang->get('CANT_CONNECT').' '.$e->getMessage());
            return;
        }

        $meta = $connection->getMeta();
        $tables = $meta->getTableNames();

        $data = [];

        foreach($tables as $v){
            $data[] = ['id'=>$v,'title'=>$v];
        }

        $this->response->success($data);
    }

    public function fieldsListAction()
    {
        $connectionId = $this->request->post('connId', 'string', false);
        $connectionType = $this->request->post('type', 'integer', false);
        $table = $this->request->post('table','string',false);

        if($connectionId === false || $connectionType===false || $table===false){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $cfg = $this->connections->getConnection($connectionType, $connectionId);

        if(!$cfg){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $conManager = new \Dvelum\Db\Manager($this->appConfig);
        try{
            $connection = $conManager->initConnection($cfg->__toArray());
        }catch (\Exception $e){
            $this->response->error($this->lang->get('CANT_CONNECT').' '.$e->getMessage());
            return;
        }

        $data = [];

        $meta = $connection->getMeta();
        $columns = $meta->getColumns($table);

        foreach ($columns as $v=>$k){
            $data[] = ['name'=>$v, 'type'=>$k->getDataType()];
        }

        $this->response->success($data);
    }

    public function externalTablesAction()
    {
        $connectionId = $this->request->post('connId', 'string', false);
        $connectionType = $this->request->post('type', 'integer', false);

        if($connectionId === false || $connectionType===false){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }


        $cfg = $this->connections->getConnection($connectionType, $connectionId);

        if(!$cfg){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }


        $cfg = $cfg->__toArray();
        try{
            $cfg['driver'] = $cfg['adapter'];
            $db = new Adapter($cfg);
            $db->query('SET NAMES ' . $cfg['charset']);
            $tables = $db->listTables();
        }catch (\Exception $e){
            $this->response->error($this->lang->get('CANT_CONNECT').' '.$e->getMessage());
            return;
        }

        $data = [];

        $manager = new Manager();
        $objects = $manager->getRegisteredObjects();

        $tablesObjects = [];

        if(!empty($objects)){
            foreach ($objects as $object) {
                $model = Model::factory($object);
                $tablesObjects[$model->table()][] = $object;
            }
        }

        if(!empty($tables))
        {
            foreach ($tables as $table)
            {
                $same = false;

                if(isset($tablesObjects[$table]) && !empty($tablesObjects[$table]))
                {
                    foreach ($tablesObjects[$table] as $oName)
                    {
                        $mCfg = Model::factory($oName)->getDbConnection()->getConfig();
                        if($mCfg['host'] === $cfg['host'] && $mCfg['dbname'] === $cfg['dbname'])
                        {
                            $same = true;
                            break;
                        }
                    }
                }
                if(!$same)
                    $data[] = array('name' => $table);
            }
        }
        $this->response->success($data);
    }

    public function connectObjectAction()
    {
        if (!$this->checkCanEdit()) {
            return;
        }

        $connectionId = $this->request->post('connId', 'string', false);
        $connectionType = $this->request->post('type', 'integer', false);
        $table = $this->request->post('table', 'string', false);

        $errors = null;

        if ($connectionId === false || $connectionType === false || $table === false) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $cfg = $this->connections->getConnection($connectionType, $connectionId);

        if (!$cfg) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }

        $cfg = $cfg->__toArray();
        try {
            $cfg['driver'] = $cfg['adapter'];
            $db = new Adapter($cfg);
            $db->query('SET NAMES ' . $cfg['charset']);
        } catch (\Exception $e) {
            $this->response->error($this->lang->get('CANT_CONNECT') . ' ' . $e->getMessage());
            return;
        }

        $import = new Import();

        if (!$import->isValidPrimaryKey($db, $table)) {
            $errors = $import->getErrors();

            if (!empty($errors)) {
                $errors = '<br>' . implode('<br>', $errors);
            } else {
                $errors = '';
            }

            $this->response->error($this->lang->get('DB_CANT_CONNECT_TABLE') . ' ' . $this->lang->get('DB_MSG_UNIQUE_PRIMARY') . ' ' . $errors);
            return;
        }

        $manager = new Manager();
        $newObjectName = strtolower(str_replace('_', '', $table));

        if ($manager->objectExists($newObjectName)) {
            $newObjectName = strtolower(str_replace('_', '', $cfg['dbname'])) . $newObjectName;
            if ($manager->objectExists($newObjectName)) {
                $k = 0;
                $alphabet = \Dvelum\Utils\Strings::alphabetEn();

                while ($manager->objectExists($newObjectName)) {
                    if (!isset($alphabet[$k])) {
                        $this->response->error('Can not create unique object name' . $errors);
                        return;
                    }

                    $newObjectName .= $alphabet[$k];
                    $k++;
                }
            }
        }

        $config = $import->createConfigByTable($db, $table, $cfg['prefix']);
        $config['connection'] = $connectionId;

        if (!$config) {
            $errors = $import->getErrors();

            if (!empty($errors)) {
                $errors = '<br>' . implode('<br>', $errors);
            } else {
                $errors = '';
            }

            $this->response->error($this->lang->get('DB_CANT_CONNECT_TABLE') . ' ' . $errors);
            return;
        } else {
            $ormConfig = Config::storage()->get('orm.php');
            $path = $ormConfig->get('object_configs') . $newObjectName . '.php';

            if (!Config::storage()->create($path)) {
                $this->response->error($this->lang->get('CANT_WRITE_FS') . ' ' . $path);
                return;
            }

            $cfg = Config::storage()->get($path, true, true);
            $cfg->setData($config);
            if (!Config::storage()->save($cfg)) {
                $this->response->error($this->lang->get('CANT_WRITE_FS') . ' ' . $path);
                return;
            }
        }
        $this->response->success();
    }
}