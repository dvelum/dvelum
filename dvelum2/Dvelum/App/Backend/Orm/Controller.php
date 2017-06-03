<?php
declare(strict_types=1);

namespace Dvelum\App\Backend\Orm;

use Dvelum\App\Backend\Orm\Manager;
use Dvelum\App\Backend\Orm\Connections;
use Dvelum\Config;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Lang;
use Dvelum\View;
use Dvelum\Template;
use Dvelum\Request;
use Dvelum\Response;
use Dvelum\App\Router\RouterInterface;
use Dvelum\Service;

class Controller extends \Dvelum\App\Backend\Controller implements RouterInterface
{
    protected $routes = [
        'dictionary' => 'Backend_Orm_Dictionary',
        'dataview' => 'Backend_Orm_Dataview',
        'connections' => 'Dvelum\\App\\Backend\\Orm\\Controller\\Connections',
        'log' => 'Dvelum\\App\\Backend\\Orm\\Controller\\Log',
        'object' => 'Dvelum\\App\\Backend\\Orm\\Controller\\Object',
        'field' => 'Dvelum\\App\\Backend\\Orm\\Controller\\Field',
        'index' => 'Dvelum\\App\\Backend\\Orm\\Controller\\Index',
        'uml' => 'Dvelum\\App\\Backend\\Orm\\Controller\\Uml',
    ];

    public function route(Request $request, Response $response) : void
    {
        $action = $request->getPart(2);
        if(isset($this->routes[$action])){
            $router = new \Dvelum\App\Router\Backend();
            $router->runController($this->routes[$action], $request->getPart(3), $request, $response);
            return;
        }

        if(method_exists($this,$action.'Action')){
            $this->{$action.'Action'}();
        }else{
            $this->indexAction();
        }
    }


    /**
     * Controller constructor.
     * @param Request $request
     * @param Response $response
     */
    public function __construct(Request $request, Response $response)
    {
        parent::__construct($request, $response);

        /*
         * Set Orm Builder log paths
         */
        $ormConfig = Config::storage()->get('orm.php');
        Orm\Object\Builder::writeLog($ormConfig['use_orm_build_log']);
        Orm\Object\Builder::setLogsPath($ormConfig['log_path']);
        Orm\Object\Builder::setLogPrefix($this->appConfig['development_version'].'_build_log.sql');
    }

    public function indexAction()
    {
        $version = Config::storage()->get('versions.php')->get('orm');
        $dbConfigs = [];

        foreach ($this->appConfig->get('db_configs') as $k=>$v){
            $dbConfigs[]= [
                'id'=>$k ,
                'title'=>$this->lang->get($v['title'])
            ];
        }
        //tooltips
        $lPath = $this->appConfig->get('language').'/orm.php';

        /**
         * @var Lang $langService
         */
        $langService = Service::get('lang');
        $langService->addLoader('orm_tooltips', $lPath, Config\Factory::File_Array);

        $this->resource->addInlineJs('
          var canPublish =  '.((integer)$this->moduleAcl->canPublish($this->module)).';
          var canEdit = '.((integer)$this->moduleAcl->canEdit($this->module)).';
          var canDelete = '.((integer)$this->moduleAcl->canDelete($this->module)).';
          var useForeignKeys = '.((integer)$this->appConfig['foreign_keys']).';
          var canUseBackup = false;
          var dbConfigsList = '.json_encode($dbConfigs).';
          var ormTooltips = '.Lang::lang('orm_tooltips')->getJson().';
        ');

        $this->resource->addJs('/js/app/system/SearchPanel.js', 0);
        $this->resource->addJs('/js/app/system/ORM.js?v='.$version, 2);

        $this->resource->addJs('/js/app/system/EditWindow.js', 1);
        $this->resource->addJs('/js/app/system/HistoryPanel.js', 1);
        $this->resource->addJs('/js/app/system/ContentWindow.js', 1);
        $this->resource->addJs('/js/app/system/RevisionPanel.js', 2);
        $this->resource->addJs('/js/app/system/RelatedGridPanel.js', 2);

        $this->resource->addJs('/js/app/system/SelectWindow.js', 2);
        $this->resource->addJs('/js/app/system/ObjectLink.js', 3);

        Model::factory('Medialib')->includeScripts();
        $this->resource->addCss('/css/system/joint.min.css', 1);
        $this->resource->addJs('/js/lib/uml/lodash.min.js', 2);
        $this->resource->addJs('/js/lib/uml/backbone-min.js', 3);
        $this->resource->addJs('/js/lib/uml/joint.min.js', 4);
        $this->resource->addJs('/js/app/system/crud/orm.js', 7);
    }


    /**
     * Get DB Objects list
     */
    public function listAction()
    {
        $stat = new Orm\Stat();
        $data = $stat->getInfo();

        if($this->request->post('hideSysObj', 'boolean', false)){
            foreach ($data as $k => $v)
                if($v['system'])
                    unset($data[$k]);
            sort($data);
        }
        $this->response->success($data);
    }

    /**
     * Build all objects action
     */
    public function buildAllAction()
    {
        $this->checkCanEdit();

        $names = $this->request->post('names', 'array', false);

        if(empty($names))
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        $flag = false;

        if(Orm\Object\Builder::foreignKeys())
        {
            /*
             * build only fields
             */
            foreach ($names as $name)
            {
                try{
                    $builder = Orm\Object\Builder::factory($name);
                    $builder->build(false);
                }catch(\Exception $e){
                    $flag = true;
                }
            }

            /*
             * Add foreign keys
             */
            foreach ($names as $name)
            {
                try{
                    $builder = Orm\Object\Builder::factory($name);
                    if(!$builder->buildForeignKeys(true , true))
                        $flag = true;
                }catch(\Exception $e){
                    $flag = true;
                }
            }

        }else{
            foreach ($names as $name)
            {
                try{
                    $builder = Orm\Object\Builder::factory($name);
                    $builder->build();
                }catch(\Exception $e){
                    $flag = true;
                }
            }
        }

        if ($flag)
            $this->response->error($this->lang->get('CANT_EXEC'));
        else
            $this->response->success();
    }

    /**
     * Get list of database connections
     */
    public function connectionsListAction()
    {
        $manager = new Connections($this->appConfig->get('db_configs'));
        $list = $manager->getConnections(0);
        $data = [];
        if(!empty($list)) {
            foreach($list as $k=>$v) {
                $data[] = ['id'=> $k];
            }
        }
        $this->response->success($data);
    }

    /**
     * Get list of ACL adapters
     */
    public function listAclAction()
    {
        $list = [['id'=>'','title'=>'---']];
        $files = \File::scanFiles('./dvelum/app/Acl', array('.php'), true, \File::Files_Only);
        foreach ($files as $v){
            $path = str_replace('./dvelum/app/', '', $v);
            $name = \Utils::classFromPath($path);
            $list[] = ['id'=>$name,'title'=>$name];
        }
        $this->response->success($list);
    }


    /*
     * Get connection types (prod , dev , test ... etc)
    */
    public function connectionTypesAction()
    {
        $data = array();
        foreach ($this->appConfig->get('db_configs') as $k=>$v){
            $data[]= ['id'=>$k , 'title'=>$this->lang->get($v['title'])];
        }
        $this->response->success($data);
    }

    /*
     * Get list of field validators
     */
    public function listValidatorsAction()
    {
        $validators = [];
        $files = \File::scanFiles('./dvelum/library/Validator', array('.php'), false, \File::Files_Only);

        foreach ($files as $v)
        {
            $name = substr(basename($v), 0, -4);
            if($name != 'Interface')
                $validators[] = ['id'=>'Validator_'.$name, 'title'=>$name];
        }

        $this->response->success($validators);
    }

    /**
     * Dev. method. Compile JavaScript sources
     */
    public function compileAction()
    {
        $sources = array(
            'js/app/system/orm/panel.js',
            'js/app/system/orm/dataGrid.js',
            'js/app/system/orm/objectWindow.js',
            'js/app/system/orm/fieldWindow.js',
            'js/app/system/orm/indexWindow.js',
            'js/app/system/orm/dictionaryWindow.js',
            'js/app/system/orm/objectsMapWindow.js',
            'js/app/system/orm/dataViewWindow.js',
            'js/app/system/orm/objectField.js',
            'js/app/system/orm/connections.js',
            'js/app/system/orm/logWindow.js',
            'js/app/system/orm/import.js',
            'js/app/system/orm/taskStatusWindow.js',
            'js/app/system/orm/selectObjectsWindow.js'
        );

        if(!$this->appConfig->get('development')){
            die('Use development mode');
        }

        $s = '';
        $totalSize = 0;

        $wwwPath = $this->appConfig->get('wwwPath');
        foreach ($sources as $filePath){
            $s.=file_get_contents($wwwPath.$filePath)."\n";
            $totalSize+=filesize($wwwPath.$filePath);
        }

        $time = microtime(true);
        file_put_contents($wwwPath.'js/app/system/ORM.js', \Code_Js_Minify::minify($s));
        echo '
			Compilation time: '.number_format(microtime(true)-$time,5).' sec<br>
			Files compiled: '.sizeof($sources).' <br>
			Total size: '.\Utils::formatFileSize($totalSize).'<br>
			Compiled File size: '.\Utils::formatFileSize(filesize($wwwPath.'js/app/system/ORM.js')).' <br>
		';
        exit;
    }

}