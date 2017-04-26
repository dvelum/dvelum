<?php
namespace Dvelum\App\Backend;

use Dvelum\App;
use Dvelum\Config;
use Dvelum\Orm\Model;
use Dvelum\App\Session;
use Dvelum\Lang;
use Dvelum\View;

class Controller extends App\Controller
{
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
     * Current Orm\Object name
     * @var string
     */
    protected $objectName = false;

    /**
     * @var Config\Adapter
     */
    protected $backofficeConfig;

    /**
     * @var App\Module\Acl
     */
    protected $moduleAcl;

    /**
     * Link to User object (current user)
     * @var Session\User
     */
    protected $user;

    public function __construct()
    {
        parent::__construct();

        $this->configBackend = Config::storage()->get('backend.php');
        $this->config = $this->getConfig();
        $this->module = $this->getModule();
        $this->objectName = $this->getObjectName();
        $this->lang = Lang::lang();
        $this->initSession();
    }

    protected function initSession()
    {
        $auth = new App\Backend\Auth($this->request, $this->appConfig);

        if($this->request->get('logout' , 'boolean' , false)){
            $auth->logout();
            if(!$this->request->isAjax()){
                $this->response->redirect($this->request->url([$this->appConfig->get('adminPath')]));
            }
        }

        $this->user = $auth->auth();

        if(!$this->user->isAuthorized() || !$this->user->isAdmin())
        {
            if($this->request->isAjax()){
                $this->response->error($this->lang->get('MSG_AUTHORIZE'));
            }else{
                $this->loginAction();
                return;
            }
        }
        $this->moduleAcl = $this->user->getModuleAcl();
        
        /*
         * Check is valid module requested
         */
        $this->validateModule();

       /*
        * Check CSRF token
        */
        if($this->configBackend->get('use_csrf_token') && $this->request->hasPost()) {
           $this->validateCsrfToken();
        }

        $this->checkCanView();
    }

    /**
     * Check view permissions
     */
    protected function checkCanView()
    {
        if(!$this->moduleAcl->canView($this->module)){
            $this->response->error($this->lang->get('CANT_VIEW'));
        }
    }

    /**
     * Check edit permissions
     */
    protected function checkCanEdit()
    {
        if(!$this->moduleAcl->canEdit($this->module)){
            $this->response->error($this->lang->get('CANT_MODIFY'));
        }
    }
    /**
     * Check delete permissions
     */
    protected function checkCanDelete()
    {
        if(!$this->moduleAcl->canDelete($this->module)){
            $this->response->error($this->lang->get('CANT_DELETE'));
        }
    }


    protected function validateCsrfToken()
    {
        $csrf = new \Security_Csrf();
        $csrf->setOptions([
            'lifetime' => $this->configBackend->get('use_csrf_token_lifetime'),
            'cleanupLimit' => $this->configBackend->get('use_csrf_token_garbage_limit')
        ]);

        if(!$csrf->checkHeader() && !$csrf->checkPost()){
            $this->response->error($this->lang->get('MSG_NEED_CSRF_TOKEN'));
        }
    }

    protected function validateModule()
    {
        $moduleManager = new \Modules_Manager();

        if(in_array($this->module, $this->configBackend->get('system_controllers'),true) || $this->module == 'index'){
            return;
        }

        /*
         * Redirect for undefined module
         */
        if(!$moduleManager->isValidModule($this->module))
            $this->response->error($this->lang->get('WRONG_REQUEST'));

        $moduleCfg = $moduleManager->getModuleConfig($this->module);

        /*
         * disabled module
         */
        if($moduleCfg['active'] == false)
            $this->response->error($this->lang->get('CANT_VIEW'));

        /*
         * dev module at production
         */
        if($moduleCfg['dev'] && ! $this->appConfig['development'])
            $this->response->error($this->lang->get('CANT_VIEW'));
    }



    /**
     * Get controller configuration
     * @return Config\Adapter
     */
    protected function getConfig() : Config\Adapter
    {
        return Config::storage()->get('backend/controller.php');
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
     * Get name of the object, which edits the controller
     * @return string
     */
    public function getObjectName() : string
    {
        return str_replace(array('Backend_', '_Controller','\\Backend\\','\\Controller') , '' , get_called_class());
    }

    /**
     * Default action
     */
    public function indexAction()
    {
        $this->includeScripts();

        $this->resource->addInlineJs('
	        var canEdit = ' . intval($this->moduleAcl->canEdit($this->module)) . ';
	        var canDelete = ' . intval($this->moduleAcl->canDelete($this->module)) . ';
	    ');

        $objectConfig = \Dvelum\Orm\Object\Config::factory($this->getObjectName());

        if($objectConfig->isRevControl()){
            $this->resource->addInlineJs('
	        var canPublish = ' . intval($this->moduleAcl->canPublish($this->module)) . ';
	    ');
        }

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
     * Show login form
     */
    protected function loginAction()
    {
        $template = new View();
        $template->set('wwwRoot' , $this->appConfig->get('wwwroot'));
        $this->response->put($template->render('system/'.$this->configBackend->get('theme') . '/login.php'));
        $this->response->send();
    }



}