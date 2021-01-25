<?php
declare(strict_types=1);

namespace Dvelum\App\Backend\Orm;

use Dvelum\Config;
use Dvelum\File;
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Lang;
use Dvelum\Request;
use Dvelum\Response;
use Dvelum\App\Router\RouterInterface;
use Dvelum\Service;
use Dvelum\Utils;

class Controller extends \Dvelum\App\Backend\Controller implements RouterInterface
{
    /**
     * @var Config\ConfigInterface
     */
    protected $routes;

    public function route(Request $request, Response $response): void
    {
        $this->routes = Config::storage()->get('orm/routes.php');

        $action = $request->getPart(2);
        if (isset($this->routes[$action])) {
            $router = new \Dvelum\App\Router\Backend();
            $router->runController($this->routes[$action], $request->getPart(3), $request, $response);
            return;
        }

        if (method_exists($this, $action . 'Action')) {
            $this->{$action . 'Action'}();
        } else {
            $this->indexAction();
        }
    }

    public function getModule(): string
    {
        return 'Orm';
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
        Orm\Record\Builder::writeLog($ormConfig['use_orm_build_log']);
        Orm\Record\Builder::setLogsPath($ormConfig['log_path']);
        Orm\Record\Builder::setLogPrefix($this->appConfig['development_version'] . '_build_log.sql');
    }

    public function indexAction()
    {
        $dbConfigs = [];

        foreach ($this->appConfig->get('db_configs') as $k => $v) {
            $dbConfigs[] = [
                'id' => $k,
                'title' => $this->lang->get($v['title'])
            ];
        }
        //tooltips
        $lPath = $this->appConfig->get('language') . '/orm.php';

        /**
         * @var Lang $langService
         */
        $langService = Service::get('lang');
        $langService->addLoader('orm_tooltips', $lPath, Config\Factory::File_Array);

        $this->resource->addInlineJs('
          var canPublish =  ' . ((integer)$this->moduleAcl->canPublish($this->module)) . ';
          var canEdit = ' . ((integer)$this->moduleAcl->canEdit($this->module)) . ';
          var canDelete = ' . ((integer)$this->moduleAcl->canDelete($this->module)) . ';
          var useForeignKeys = ' . ((integer)$this->appConfig['foreign_keys']) . ';
          var canUseBackup = false;
          var dbConfigsList = ' . json_encode($dbConfigs) . ';
          var ormTooltips = ' . Lang::lang('orm_tooltips')->getJson() . ';
          var shardingEnabled = ' . intval(Config::storage()->get('orm.php')->get('sharding')) . ';
          var ormActionsList = '.json_encode($this->getActions()).';
          var ormAddObjectFields = ['.$this->getAdditionalObjectFields().'];
        ');

        $this->resource->addJs('/js/app/system/SearchPanel.js', 0);
        $this->resource->addJs('/js/app/system/ORM.js', 2, true);

        $this->resource->addJs('/js/app/system/EditWindow.js', 1);
        $this->resource->addJs('/js/app/system/HistoryPanel.js', 1);
        $this->resource->addJs('/js/app/system/ContentWindow.js', 1);
        $this->resource->addJs('/js/app/system/RevisionPanel.js', 2);
        $this->resource->addJs('/js/app/system/RelatedGridPanel.js', 2);
        $this->resource->addJs('/js/lib/ext_ux/rowExpanderGrid.js', 2);


        $this->resource->addJs('/js/app/system/SelectWindow.js', 2);
        $this->resource->addJs('/js/app/system/ObjectLink.js', 3);

        $designerConfig = Config::storage()->get('designer.php');
        /**
         * @todo refactor
         * include Media Library if html editor installed
         *  moved to dvelum/module-cms
         */
        if($designerConfig->get('html_editor')){
            Model::factory('Medialib')->includeScripts();
        }

        $this->resource->addCss('/css/system/joint.min.css', 1);

        $this->resource->addJs('/js/lib/jquery.js', 1, true, 'external');
        $this->resource->addJs('/js/lib/uml/lodash.min.js', 2, true, 'external');
        $this->resource->addJs('/js/lib/uml/backbone-min.js', 3, true, 'external');
        $this->resource->addJs('/js/lib/uml/joint.min.js', 4, true, 'external');
        $this->resource->addJs('/js/app/system/crud/orm.js', 7);
    }

    /**
     * Get list of ORM actions
     */
    protected function getActions() : array
    {
        $controllerCode = $this->request->getPart(1);
        $adminPath = $this->appConfig->get('adminPath');
        $appRoot = $this->request->url([$adminPath, $controllerCode, '']);

        $config = Config::storage()->get('orm/actions.php');
        $list = $config->__toArray();
        foreach ($list as &$v){
            $v = $appRoot . $v;
        }
        return $list;
    }

    protected function getAdditionalObjectFields():string
    {
        $config = Config::storage()->get('orm/properties.php')->__toArray();

        if(empty($config)){
            return '';
        }
        $fieldsJs = Utils::fetchCol('js_field', $config);
        return implode(',', array_values($fieldsJs));
    }


    /**
     * Get DB Objects list
     */
    public function listAction()
    {
        $stat = new Orm\Stat();
        $data = $stat->getInfo();

        if ($this->request->post('hideSysObj', 'boolean', false)) {
            foreach ($data as $k => $v) {
                if ($v['system']) {
                    unset($data[$k]);
                }
            }
            sort($data);
        }
        $this->response->success($data);
    }

    /**
     * Get Data info
     */
    public function listDetailsAction()
    {
        $object = $this->request->post('object', 'string', '');

        if (!Orm\Record\Config::configExists($object)) {
            $this->response->error($this->lang->get('WRONG_REQUEST'));
            return;
        }
        $stat = new Orm\Stat();
        $config = Orm\Record\Config::factory($object);
        if ($config->isDistributed()) {
            $data = $stat->getDistributedDetails($object);
        } else {
            $data = $stat->getDetails($object);
        }
        $this->response->success($data);
    }

    /**
     * Build all objects action
     */
    public function buildAllAction()
    {
        if (!$this->checkCanEdit()) {
            return;
        }

        session_write_close();

        $dbObjectManager = new Orm\Record\Manager();
        $names = $dbObjectManager->getRegisteredObjects();
        if(empty($names)){
            $names = [];
        }

        $flag = false;
        $ormConfig = Config::storage()->get('orm.php');
        if ($ormConfig->get('foreign_keys')) {
            /*
             * build only fields
             */
            foreach ($names as $name) {
                try {
                    $builder = Orm\Record\Builder::factory($name);
                    $builder->build(false);
                } catch (\Exception $e) {
                    $flag = true;
                }
            }
           /*
            * Add foreign keys
            */
            foreach ($names as $name) {
                try {
                    $builder = Orm\Record\Builder::factory($name);
                    if (!$builder->buildForeignKeys(true, true)) {
                        $flag = true;
                    }
                } catch (\Exception $e) {
                    $flag = true;
                }
            }
        } else {
                foreach ($names as $name) {
                    try {
                        $builder = Orm\Record\Builder::factory($name);
                        $builder->build();
                    } catch (\Exception $e) {
                        $flag = true;
                    }
                }
        }

        if ($ormConfig->get('sharding')) {
            $sharding = Config::storage()->get('sharding.php');
            $shardsFile = $sharding->get('shards');
            $shardsConfig = Config::storage()->get($shardsFile);
            $registeredObjects = $dbObjectManager->getRegisteredObjects();
            if(empty($registeredObjects)){
                $registeredObjects = [];
            }

            foreach ($shardsConfig as $item) {
                $shardId = $item['id'];
                //build objects
                foreach ($names as $index => $object) {
                    if (!Orm\Record\Config::factory($object)->isDistributed()) {
                        unset($registeredObjects[$index]);
                        continue;
                    }
                    $builder = Orm\Record\Builder::factory($object);
                    $builder->setConnection(Orm\Model::factory($object)->getDbShardConnection($shardId));
                    if (!$builder->build(false, true)) {
                        $flag = true;
                    }
                }

                //build foreign keys
                if ($ormConfig->get('foreign_keys')) {
                    foreach ($registeredObjects as $index => $object) {
                        $builder = Orm\Record\Builder::factory($object);
                        $builder->setConnection(Orm\Model::factory($object)->getDbShardConnection($shardId));
                        if (!$builder->build(true, true)) {
                            $flag = true;
                        }
                    }
                }
            }
        }


        if ($flag) {
            $this->response->error($this->lang->get('CANT_EXEC'));
        } else {
            $this->response->success();
        }
    }

    /**
     * Get list of database connections
     */
    public function connectionsListAction()
    {
        $manager = new Connections($this->appConfig->get('db_configs'));
        $list = $manager->getConnections(0);
        $data = [];
        if (!empty($list)) {
            foreach ($list as $k => $v) {
                $data[] = ['id' => $k];
            }
        }
        $this->response->success($data);
    }

    /*
     * Get connection types (prod , dev , test ... etc)
    */
    public function connectionTypesAction()
    {
        $data = [];
        foreach ($this->appConfig->get('db_configs') as $k => $v) {
            $data[] = ['id' => $k, 'title' => $this->lang->get($v['title'])];
        }
        $this->response->success($data);
    }

    /*
     * Get list of field validators
     */
    public function listValidatorsAction()
    {
        $validators = [];
        $files = File::scanFiles('./extensions/dvelum-core/src/Dvelum/Validator', ['.php'], false, File::FILES_ONLY);

        foreach ($files as $v) {
            $name = substr(basename($v), 0, -4);
            if ($name != 'ValidatorInterface') {
                $validators[] = ['id' => '\\Dvelum\\Validator\\' . $name, 'title' => $name];
            }
        }

        $this->response->success($validators);
    }

    /**
     * Dev. method. Compile JavaScript sources
     */
    public function compileAction()
    {
        $sources = [
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
            'js/app/system/orm/selectObjectsWindow.js',
            'js/app/system/orm/validate.js'

        ];

        if (!$this->appConfig->get('development')) {
            die('Use development mode');
        }

        $s = '';
        $totalSize = 0;

        $wwwPath = $this->appConfig->get('wwwPath');
        foreach ($sources as $filePath) {
            $s .= file_get_contents($wwwPath . $filePath) . "\n";
            $totalSize += filesize($wwwPath . $filePath);
        }

        $time = microtime(true);
        file_put_contents($wwwPath . 'js/app/system/ORM.js', \Dvelum\App\Code\Minify\Minify::factory()->minifyJs($s));
        echo '
            Compilation time: ' . number_format(microtime(true) - $time, 5) . ' sec<br>
            Files compiled: ' . sizeof($sources) . ' <br>
            Total size: ' . Utils::formatFileSize($totalSize) . '<br>
            Compiled File size: ' . Utils::formatFileSize((int) filesize($wwwPath . 'js/app/system/ORM.js')) . ' <br>
        ';
        exit;
    }

    /**
     * Find url
     * @param string $module
     * @return string
     */
    public function findUrl(string $module): string
    {
        return '';
    }

    /**
     * Get desktop module info
     */
    public function desktopModuleInfo()
    {
        $version = Config::storage()->get('versions.php')->get('orm');
        $dbConfigs = [];
        foreach ($this->appConfig->get('db_configs') as $k => $v) {
            $dbConfigs[] = array('id' => $k, 'title' => $this->lang->get($v['title']));
        }

        //tooltips
        $lPath = $this->appConfig->get('language') . '/orm.php';
        Lang::addDictionaryLoader('orm_tooltips', $lPath, Config\Factory::File_Array);
        $projectData['includes']['js'][] = $this->resource->cacheJs('
           var useForeignKeys = ' . ((integer)$this->appConfig['foreign_keys']) . ';
           var dbConfigsList = ' . json_encode($dbConfigs) . ';
           var ormTooltips = ' . Lang::lang('orm_tooltips')->getJson() . ';
        ');

        $projectData['includes']['css'][] = '/css/system/joint.min.css';
        $projectData['includes']['js'][] = '/js/lib/uml/lodash.min.js';
        $projectData['includes']['js'][] = '/js/lib/uml/backbone-min.js';
        $projectData['includes']['js'][] = '/js/lib/uml/joint.min.js';
        $projectData['includes']['js'][] = '/js/lib/ext_ux/rowExpanderGrid.js';
        $projectData['includes']['js'][] = '/js/app/system/ORM.js?v=' . $version;
        /*
         * Module bootstrap
         */
        if (file_exists($this->appConfig->get('jsPath') . 'app/system/desktop/' . strtolower($this->getModule()) . '.js')) {
            $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->getModule()) . '.js';
        }
        return $projectData;
    }
}