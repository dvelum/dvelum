<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
 * Kirill A Egorov
 *
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 */
use Dvelum\Orm\Model;
use Dvelum\Config;
use Dvelum\Orm;
use Dvelum\Request;
use Dvelum\App;
use Dvelum\App\Data;
use Dvelum\App\Session;

/**
 * This is the base class for implementing administrative controllers
 */
abstract class Backend_Controller extends \Controller
{
    /**
     * Module id assigned to controller;
     * Is to be defined in child class
     * Is used for controlling access permissions
     *
     * @var string
     */
    protected $_module;

    /**
     * Link to Config object of the connected JS files
     *
     * @var Config_Abstract
     */
    protected $_configJs;

    /**
     * Link to User object (current user)
     *
     * @var User
     */
    protected $_user;

    /**
     * The checkbox signifies whether the current request has
     * been sent using AJAX
     */
    protected $_isAjaxRequest;

    /**
     * Controller config
     * @var \Dvelum\Config $config
     */
    protected $config;

    /**
     * @var \Dvelum\App\Controller\EventManager
     */
    protected $eventManager;

    public function __construct()
    {
        parent::__construct();

        $this->eventManager = new App\Controller\EventManager();
        $this->config = $this->getConfig();
        $cacheManager = new \Cache_Manager();
        $this->_module = $this->getModule();
        $this->_configBackend = Config::storage()->get('backend.php');
        $this->_cache = $cacheManager->get('data');
        $this->response = \Dvelum\Response::factory();

        $this->initSession();
    }

    protected function initSession()
    {
        $auth = new App\Backend\Auth($this->request, $this->_configMain);

        if($this->request->get('logout' , 'boolean' , false)){
            $auth->logout();
            if(!$this->request->isAjax()){
                $this->response->redirect($this->request->url([$this->_configMain->get('adminPath')]));
            }
        }

        $this->_user = $auth->auth();

        if(!$this->_user->isAuthorized() || !$this->_user->isAdmin())
        {
            if($this->request->isAjax()){
                $this->response->error($this->_lang->get('MSG_AUTHORIZE'));
            }else{
                $this->loginAction();
                return;
            }
        }

        $this->moduleAcl = $this->_user->getModuleAcl();

        /*
         * Check is valid module requested
         */
        $this->validateModule();

        /*
         * Check CSRF token
         */
        if($this->_configBackend->get('use_csrf_token') && $this->request->hasPost()) {
            $this->validateCsrfToken();
        }

        $this->checkCanView();

    }

    protected function validateCsrfToken()
    {
        $csrf = new \Security_Csrf();
        $csrf->setOptions([
            'lifetime' => $this->_configBackend->get('use_csrf_token_lifetime'),
            'cleanupLimit' => $this->_configBackend->get('use_csrf_token_garbage_limit')
        ]);

        if(!$csrf->checkHeader() && !$csrf->checkPost()){
            $this->response->error($this->_lang->get('MSG_NEED_CSRF_TOKEN'));
        }
    }

    protected function validateModule()
    {
        $moduleManager = new \Modules_Manager();

        if(in_array($this->_module, $this->_configBackend->get('system_controllers'),true)){
            return;
        }

        /*
         * Redirect for undefined module
         */
        if(!$moduleManager->isValidModule($this->_module))
            $this->response->error($this->_lang->get('WRONG_REQUEST'));

        $moduleCfg = $moduleManager->getModuleConfig($this->_module);

        /*
         * disabled module
         */
        if($moduleCfg['active'] == false)
            $this->response->error($this->_lang->get('CANT_VIEW'));

        /*
         * dev module at production
         */
        if($moduleCfg['dev'] && ! $this->_configMain['development'])
            $this->response->error($this->_lang->get('CANT_VIEW'));
    }

    /**
     * Get controller configuration
     */
    protected function getConfig()
    {
        return Config::storage()->get('backend/controller.php');
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
                    $this->_resource->addJs($file , $config['order'] , $config['minified']);

            $css = $cfg->get('css');
            if(!empty($css))
                foreach($css as $file => $config)
                    $this->_resource->addCss($file , $config['order']);
        }

    }
    /**
     * Send JSON error message
     *
     * @return string
     */
    protected function _errorResponse($msg)
    {
        if(Request::isAjax())
            Response::jsonError($msg);
        else
            Response::redirect(Request::url(array($this->_configMain->get('adminPath'))));
    }
    /**
     * Get module name of the current class
     *
     * @return string
     */
    public function getModule()
    {
        $manager = new \Modules_Manager();
        return $manager->getControllerModule(get_called_class());
    }

    /**
     * Check view permissions
     */
    protected function checkCanView()
    {
        if(!$this->moduleAcl->canView($this->_module)){
            $this->response->error($this->_lang->get('CANT_VIEW'));
        }
    }

    /**
     * Show login form
     */
    protected function loginAction()
    {
        $configBackend = Config::storage()->get('backend.php');

        $template = new Template();
        $template->set('wwwRoot' , $this->_configMain->get('wwwroot'));
        Response::put($template->render('system/'.$configBackend->get('theme') . '/login.php'));
        Application::close();
    }
    /**
     * Get posted data and put it into Db_Object
     * (in case of failure, JSON error message is sent)
     *
     * @param string $objectName
     * @return \Dvelum\Orm\Object
     */
    public function getPostedData($objectName)
    {
        $formCfg = $this->config->get('form');
        $adapterConfig = Config::storage()->get($formCfg['config']);
        $adapterConfig->set('orm_object', $objectName);
        /**
         * @var \Dvelum\App\Form\Adapter $form
         */
        $form = new $formCfg['adapter'](
            $this->request,
            $this->_lang,
            $adapterConfig
        );

        if(!$form->validateRequest())
        {
            $errors = $form->getErrors();
            $formMessages = [$this->_lang->get('FILL_FORM')];
            $fieldMessages = [];
            /**
             * @var \Dvelum\App\Form\Error $item
             */
            foreach ($errors as $item)
            {
                $field = $item->getField();
                if(empty($field)){
                    $formMessages[] = $item->getMessage();
                }else{
                    $fieldMessages[$field] = $item->getMessage();
                }
            }
            Response::jsonError(implode('; <br>', $formMessages) , $fieldMessages);
        }
        return $form->getData;
    }
    /**
     * Check edit permissions
     */
    protected function _checkCanEdit()
    {
        if(!User::getInstance()->canEdit($this->_module))
            Response::jsonError($this->_lang->CANT_MODIFY);
    }
    /**
     * Check delete permissions
     */
    protected function _checkCanDelete()
    {
        if(!User::getInstance()->canDelete($this->_module))
            Response::jsonError($this->_lang->CANT_DELETE);
    }
    /**
     * Default action
     */
    public function indexAction()
    {
        $this->includeScripts();

        $this->_resource->addInlineJs('
	        var canEdit = ' . intval($this->_user->canEdit($this->_module)) . ';
	        var canDelete = ' . intval($this->_user->canDelete($this->_module)) . ';
	    ');

        $this->includeScripts();

        $modulesConfig = Config\Factory::config(Config\Factory::File_Array , $this->_configMain->get('backend_modules'));
        $moduleCfg = $modulesConfig->get($this->_module);

        if(strlen($moduleCfg['designer']))
            $this->_runDesignerProject($moduleCfg['designer']);
        else
            if(file_exists($this->_configMain->get('jsPath').'app/system/crud/' . strtolower($this->_module) . '.js'))
                $this->_resource->addJs('/js/app/system/crud/' . strtolower($this->_module) .'.js' , 4);
    }
    /**
     * Run designer project
     * @param string $project - path to project file
     * @param string | boolean $renderTo
     */
    protected function _runDesignerProject($project , $renderTo = false)
    {
        $manager = new \Designer_Manager($this->_configMain);
        $project = $manager->findWorkingCopy($project);
        $manager->renderProject($project, $renderTo, $this->_module);
    }
    /**
     * Get desktop module info
     */
    protected function desktopModuleInfo()
    {
        $modulesConfig = Config::factory(Config\Factory::File_Array , $this->_configMain->get('backend_modules'));
        $moduleCfg = $modulesConfig->get($this->_module);

        $projectData = [];

        if(strlen($moduleCfg['designer']))
        {
            $manager = new \Designer_Manager($this->_configMain);
            $project = $manager->findWorkingCopy($moduleCfg['designer']);
            $projectData =  $manager->compileDesktopProject($project, 'app.__modules.'.$this->_module , $this->_module);
            $projectData['isDesigner'] = true;
            $modulesManager = new \Modules_Manager();
            $modulesList = $modulesManager->getList();
            $projectData['title'] = (isset($modulesList[$this->_module])) ? $modulesList[$this->_module]['title'] : '';
        }
        else
        {
            if(file_exists($this->_configMain->get('jsPath').'app/system/desktop/' . strtolower($this->_module) . '.js'))
                $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($this->_module) .'.js';
        }
        return $projectData;
    }
}