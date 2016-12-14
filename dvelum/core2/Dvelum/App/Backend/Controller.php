<?php
namespace Dvelum\App\Backend;

use Dvelum\App;
use Dvelum\Config;
use Dvelum\Model;
use Dvelum\Orm;
use Dvelum\App\Data;
use Dvelum\Request;
use Dvelum\App\Session;
use Dvelum\Lang;
use Dvelum\App\Controller\EventManager;

class Controller extends App\Controller
{

    /**
     * Controller events manager
     * @var App\Controller\EventManager
     */
    protected $eventManager;

    /**
     * Controller configuration
     * @var Config\Config
     */
    protected $config;
    /**
     * Localization adapter
     * @var Lang
     */
    protected $lang;
    /**
     * Module id assigned to controller;
     * Is to be defined in child class
     * Is used for controlling access permissions
     *
     * @var string
     */
    protected $module;
    /**
     * Current Db_Object name
     * @var string
     */
    protected $objectName = false;
    /**
     * List of ORM objects accepted via linkedListAction and otitleAction
     * @var array
     */
    protected $canViewObjects = [];
    /**
     * List of ORM object link fields displayed with related values in the main list (listAction)
     * (dictionary, object link, object list) key - result field, value - object field
     * object field will be used as result field for numeric keys
     * Requires primary key in result set
     * @var array
     */
    protected $listLinks = [];

    /**
     * API Request object
     * @var Data\Api\Request
     */
    protected $apiRequest;

    /**
     * Link to User object (current user)
     * @var Session\User
     */
    protected $user;

    public function __construct()
    {
        parent::__construct();

        $this->eventManager = new App\Controller\EventManager();

        $this->config = $this->getConfig();
        $this->module = $this->getModule();
        $this->objectName = $this->getObjectName();
        $this->canViewObjects[] = $this->objectName;
        $this->canViewObjects = \array_map('strtolower', $this->canViewObjects);
        $this->apiRequest = $this->getApiRequest($this->request);
        $this->lang = Lang::lang();

        $this->initListeners();

        $this->checkLogout();
        $this->checkAuth();
    }

    /**
     * Get controller configuration
     * @return Config\Config
     */
    protected function getConfig() : Config\Config
    {
        return Config::storage()->get('backend/controller.php');
    }

    protected function checkLogout()
    {
        if($this->request->get('logout' , 'boolean' , false)){
            App\Session\User::factory()->logout();
            session_destroy();
            if(!$this->request->isAjax()){
                $this->response->redirect($this->request->url(array($this->appConfig->get('adminPath'))));
            }
        }
    }

    /**
     * Get module name of the current class
     * @return string
     */
    public function getModule()
    {
        $manager = new \Modules_Manager();
        return $manager->getControllerModule(get_called_class());
    }

    /**
     *  Event listeners can be defined here
     */
    public function initListeners(){}

    /**
     * @param Data\Api\Request $request
     * @param Session\User $user
     * @return Data\Api
     */
    protected function getApi(Data\Api\Request $request, Session\User $user) : Data\Api
    {
        return new Data\Api($request, $user);
    }

    /**
     * @param Request $request
     * @return Data\Api\Request
     */
    protected function getApiRequest(Request $request) : Data\Api\Request
    {
        return new Data\Api\Request($request);
    }

    /**
     * Get name of the object, which edits the controller
     * @return string
     */
    public function getObjectName() : string
    {
        return str_replace(array('Backend_', '_Controller','\\Backend\\','\\Controller') , '' , get_called_class());
    }

    /**
     * Check user permissions and authentication
     */
    public function checkAuth()
    {
        $configBackend = Config::storage()->get('backend.php');

        $user = Session\User::getInstance();
        $uid = false;

        if($user->isAuthorized()) {
            $uid = $user->id;
            $userLang =$user->getLanguage();
            $langManager = new \Backend_Localization_Manager($this->appConfig);
            $acceptedLanguages = $langManager->getLangs(true);
            // switch language
            if(!empty($userLang) && $userLang!=$this->appConfig->get('language') && in_array($userLang, $acceptedLanguages , true)){
                $this->appConfig->set('language' , $userLang);
                Lang::addDictionaryLoader($userLang ,  $userLang . '.php' , Config\Factory::File_Array);
                Lang::setDefaultDictionary($userLang);
                \Dictionary::setConfigPath($this->appConfig->get('dictionary_folder') . $this->appConfig->get('language').'/');
            }
        }

        if(! $uid || ! $user->isAdmin()){
            if($this->request->isAjax())
                Response::jsonError($this->_lang->MSG_AUTHORIZE);
            else
                $this->loginAction();
        }
        /*
         * Check CSRF token
         */
        if($configBackend->get('use_csrf_token') && Request::hasPost()){
            $csrf = new \Security_Csrf();
            $csrf->setOptions(
                array(
                    'lifetime' => $configBackend->get('use_csrf_token_lifetime'),
                    'cleanupLimit' => $configBackend->get('use_csrf_token_garbage_limit')
                ));

            if(!$csrf->checkHeader() && !$csrf->checkPost())
                $this->_errorResponse($this->_lang->MSG_NEED_CSRF_TOKEN);
        }

        $this->user = $user;

        $isSysController = in_array(get_called_class() , $configBackend->get('system_controllers') , true);

        if($isSysController)
            return;

        if(!$this->user->canView($this->module)){
            $this->response->error($this->lang->get('CANT_VIEW'));
        }

        $moduleManager = new \Modules_Manager();

        /*
         * Redirect for undefined module
         */
        if(!$moduleManager->isValidModule($this->module)){
            $this->response->error($this->lang->get('WRONG_REQUEST'));
        }

        $moduleCfg = $moduleManager->getModuleConfig($this->module);

        /*
         * Redirect for disabled module
         */
        if($moduleCfg['active'] == false) {
            $this->response->error($this->lang->get('CANT_VIEW'));
        }

        /*
         * Redirect for dev module at production
         */
        if($moduleCfg['dev'] && ! $this->appConfig['development']){
            $this->response->error($this->lang->get('CANT_VIEW'));
        }
    }

    /**
     * Default action
     */
    public function indexAction()
    {
        $this->includeScripts();

        $this->resource->addInlineJs('
	        var canEdit = ' . intval($this->user->canEdit($this->module)) . ';
	        var canDelete = ' . intval($this->user->canDelete($this->module)) . ';
	    ');

        $this->includeScripts();

        $modulesConfig = Config\Factory::config(Config\Factory::File_Array , $this->appConfig->get('backend_modules'));
        $moduleCfg = $modulesConfig->get($this->module);

        if(strlen($moduleCfg['designer']))
            $this->runDesignerProject($moduleCfg['designer']);
        else
            if(file_exists($this->appConfig->get('jsPath').'app/system/crud/' . strtolower($this->module) . '.js'))
                $this->resource->addJs('/js/app/system/crud/' . strtolower($this->module) .'.js' , 4);
    }

    /**
     * Include required JavaScript files defined in the configuration file
     */
    public function includeScripts()
    {
        $media = Model::factory('Medialib');
        $media->includeScripts();
        $cfg = Config::storage()->get('js_inc_backend.php');

        if($cfg->getCount())
        {
            $js = $cfg->get('js');
            if(!empty($js))
                foreach($js as $file => $config)
                    $this->resource->addJs($file , $config['order'] , $config['minified']);

            $css = $cfg->get('css');
            if(!empty($css))
                foreach($css as $file => $config)
                    $this->resource->addCss($file , $config['order']);
        }
    }

    /**
     * Run designer project
     * @param string $project - path to project file
     * @param string | boolean $renderTo
     */
    protected function runDesignerProject($project , $renderTo = false)
    {
        $manager = new \Designer_Manager($this->appConfig);
        $project = $manager->findWorkingCopy($project);
        $manager->renderProject($project, $renderTo, $this->module);
    }
    /**
     * Get desktop module info
     */
    protected function desktopModuleInfo()
    {
        $modulesConfig = Config::factory(Config\Factory::File_Array , $this->appConfig->get('backend_modules'));
        $moduleCfg = $modulesConfig->get($this->module);

        $projectData = [];

        if(strlen($moduleCfg['designer']))
        {
            $manager = new \Designer_Manager($this->appConfig);
            $project = $manager->findWorkingCopy($moduleCfg['designer']);
            $projectData =  $manager->compileDesktopProject($project, 'app.__modules.'.$this->module , $this->module);
            $projectData['isDesigner'] = true;
            $modulesManager = new \Modules_Manager();
            $modulesList = $modulesManager->getList();
            $projectData['title'] = (isset($modulesList[$this->module])) ? $modulesList[$this->module]['title'] : '';
        }
        else
        {
            if(file_exists($this->appConfig->get('jsPath').'app/system/desktop/' . strtolower($this->module) . '.js'))
                $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->module) .'.js';
        }
        return $projectData;
    }

    /**
     * Get list of items. Returns JSON reply with
     * ORM object field data or return array with data and count;
     * Filtering, pagination and search are available
     * Sends JSON reply in the result
     * and closes the application (by default).
     * @throws Exception
     * @return void
     */
    public function listAction()
    {
        if(!$this->eventManager->fireEvent(EventManager::BEFORE_LIST, new stdClass())){
            $this->response->error($this->eventManager->getError());
        }

        $result = $this->getList();

        $eventData = new \stdClass();
        $eventData->data = $result['data'];
        $eventData->count = $result['count'];

        if(!$this->eventManager->fireEvent(EventManager::AFTER_LIST, $eventData)){
            $this->response->error($this->eventManager->getError());
        }

        $this->response->success(
            $eventData->data,
            ['count'=>$eventData->count]
        );
    }

    /**
     * Prepare data for listAction
     * backward compatibility
     * @return array
     * @throws \Exception
     */
    protected function getList()
    {
        $api = $this->getApi($this->apiRequest, $this->_user);
        $count = $api->getCount();

        if(!$count){
            return ['data'=>[],'count'=>0];
        }

        $data = $api->getList();

        if(!empty($this->listLinks)){
            $objectConfig = Orm\Object\Config::factory($this->objectName);
            /**
             * @todo refactor
             */
//
//            if(!in_array($objectConfig->getPrimaryKey(),'',true)){
//                throw new Exception('listLinks requires primary key for object '.$objectConfig->getName());
//            }

            $this->addLinkedInfo($objectConfig, $this->listLinks, $data, $objectConfig->getPrimaryKey());
        }

        return ['data' =>$data , 'count'=> $count];
    }
}