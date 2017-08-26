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
use Dvelum\App\Session\User;
use Dvelum\Config;
use Dvelum\Lang;
use Dvelum\App;
use Dvelum\Orm;
use Dvelum\Orm\ObjectInterface;
/**
 * This is the base class for implementing administrative controllers
 */
abstract class Backend_Controller extends Controller
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
     * Link to Config object of the backend application
     *
     * @var Config_Abstract
     */
    protected $_configBackend;

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


    protected $_linkedInfoSeparator = '; ';

    /**
     * @var \Dvelum\App\Controller\EventManager
     */
    protected $eventManager;
    /**
     * @var \Dvelum\Response
     */
    protected $response;
    /**
     * Controller config
     * @var \Dvelum\Config $config
     */
    protected $config;

    /**
     * @var Dvelum\App\Module\Acl
     */
    protected $moduleAcl;

    public function __construct()
    {
        parent::__construct();

        $cacheManager = new Cache_Manager();
        $this->_configBackend = Config::storage()->get('backend.php');
        $this->_module = $this->getModule();
        $this->_cache = $cacheManager->get('data');

        //backward compatibility
        $frontConfig = Config::storage()->get('frontend.php');
        $this->_configMain->set('frontend_router', 'Router_'.$frontConfig->get('router'));
        //======================

        $this->response = \Dvelum\Response::factory();
        $this->eventManager = new App\Controller\EventManager();
        $this->config = $this->getConfig();

        $this->initSession();

    }

    protected function initSession()
    {
        $auth = new App\Auth($this->request, $this->_configMain);
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

    protected function validateModule()
    {
        $moduleManager = new \Modules_Manager();
        if(in_array(get_called_class(), $this->_configBackend->get('system_controllers'),true)){
            return;
        }
        /*
         * Redirect for undefined module
         */
        if(!$moduleManager->isValidModule($this->_module)){
            $this->response->error($this->_lang->get('WRONG_REQUEST'));
            return;
        }


        $moduleCfg = $moduleManager->getModuleConfig($this->_module);
        /*
         * disabled module
         */
        if($moduleCfg['active'] == false){
            $this->response->error($this->_lang->get('CANT_VIEW'));
            return;
        }

        /*
         * dev module at production
         */
        if($moduleCfg['dev'] && ! $this->_configMain['development']){
            $this->response->error($this->_lang->get('CANT_VIEW'));
            return;
        }

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

    /**
     * Check view permissions
     */
    protected function checkCanView()
    {
        if(in_array(get_called_class(), $this->_configBackend->get('system_controllers'),true)){
            return;
        }

        if(!$this->moduleAcl->canView($this->_module)){
            $this->response->error($this->_lang->get('CANT_VIEW'));
        }
    }

    /**
     * Get controller configuration
     */
    protected function getConfig()
    {
        return Config::storage()->get('backend/controller.php');
    }


    /**
     * Add related objects info into getList results
     * @param Orm\Object\Config $cfg
     * @param array $fieldsToShow  list of link fields to process ( key - result field, value - object field)
     * object field will be used as result field for numeric keys
     * @param array & $data rows from  Model::getList result
     * @param string $pKey - name of Primary Key field in $data
     * @throws Exception
     */
    protected function addLinkedInfo(Orm\Object\Config $cfg, array $fieldsToShow, array  & $data, $pKey)
    {
        $fieldsToKeys = [];
        foreach($fieldsToShow as $key=>$val){
            if(is_numeric($key)){
                $fieldsToKeys[$val] = $val;
            }else{
                $fieldsToKeys[$val] = $key;
            }
        }

        $links = $cfg->getLinks(
            [
                Orm\Object\Config::LINK_OBJECT,
                Orm\Object\Config::LINK_OBJECT_LIST,
                Orm\Object\Config::LINK_DICTIONARY
            ],
            false
        );

        foreach($fieldsToShow as $resultField => $objectField)
        {
            if(!isset($links[$objectField]))
                throw new Exception($objectField.' is not Link');
        }

        foreach ($links as $field=>$config)
        {
            if(!isset($fieldsToKeys[$field])){
                unset($links[$field]);
            }
        }

        $rowIds = Utils::fetchCol($pKey , $data);
        $rowObjects = Orm\Object::factory($cfg->getName() , $rowIds);
        $listedObjects = [];

        foreach($rowObjects as $object)
        {
            foreach ($links as $field=>$config)
            {
                if($config['link_type'] === Orm\Object\Config::LINK_DICTIONARY){
                    continue;
                }

                if(!isset($listedObjects[$config['object']])){
                    $listedObjects[$config['object']] = [];
                }

                $oVal = $object->get($field);

                if(!empty($oVal))
                {
                    if(!is_array($oVal)){
                        $oVal = [$oVal];
                    }
                    $listedObjects[$config['object']] = array_merge($listedObjects[$config['object']], array_values($oVal));
                }
            }
        }

        foreach($listedObjects as $object => $ids){
            $listedObjects[$object] = Db_Object::factory($object, array_unique($ids));
        }

        foreach ($data as &$row)
        {
            if(!isset($rowObjects[$row[$pKey]]))
                continue;

            foreach ($links as $field => $config)
            {
                $list = [];
                $rowObject = $rowObjects[$row[$pKey]];
                $value = $rowObject->get($field);

                if(!empty($value))
                {
                    if($config['link_type'] === Orm\Object\Config::LINK_DICTIONARY)
                    {
                        $dictionary = Dictionary::factory($config['object']);
                        if($dictionary->isValidKey($value)){
                            $row[$fieldsToKeys[$field]] = $dictionary->getValue($value);
                        }
                        continue;
                    }

                    if(!is_array($value))
                        $value = [$value];

                    foreach($value as $oId)
                    {
                        if(isset($listedObjects[$config['object']][$oId])){
                            $list[] = $this->linkedInfoObjectRenderer($rowObject, $field, $listedObjects[$config['object']][$oId]);
                        }else{
                            $list[] = '[' . $oId . '] ('.$this->_lang->get('DELETED').')';
                        }
                    }
                }
                $row[$fieldsToKeys[$field]] =  implode($this->_linkedInfoSeparator, $list);
            }
        }unset($row);
    }

    /**
     * String representation of related object for addLinkedInfo method
     * @param ObjectInterface $rowObject
     * @param string $field
     * @param ObjectInterface $relatedObject
     * @return string
     */
    protected function linkedInfoObjectRenderer(ObjectInterface $rowObject, $field, ObjectInterface $relatedObject)
    {
        return $relatedObject->getTitle();
    }



    /**
     * Include required JavaScript files defined in the configuration file
     */
    public function includeScripts()
    {
        /**
         * @var Model_Medialib $media
         */
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
     * @return string
     */
    public function getModule()
    {
        $manager = new Modules_Manager();
        return $manager->getControllerModule(get_called_class());
    }
    /**
     * Check user permissions and authentication
     */
    public function checkAuth()
    {
        $user = User::getInstance();
        $uid = false;

        if($user->isAuthorized()) {
            $uid = $user->id;
            $userLang =$user->getLanguage();
            $langManager = new Backend_Localization_Manager($this->_configMain);
            $acceptedLanguages = $langManager->getLangs(true);
            // switch language
            if(!empty($userLang) && $userLang!=$this->_configMain->get('language') && in_array($userLang, $acceptedLanguages , true)){
                $this->_configMain->set('language' , $userLang);
                Lang::addDictionaryLoader($userLang ,  $userLang . '.php' , Config\Factory::File_Array);
                Lang::setDefaultDictionary($userLang);
                Dictionary::setConfigPath($this->_configMain->get('dictionary_folder') . $this->_configMain->get('language').'/');
            }
        }

        if(! $uid || ! $user->isAdmin()){
            if(Request::isAjax())
                Response::jsonError($this->_lang->MSG_AUTHORIZE);
            else
                $this->loginAction();
        }
        /*
         * Check CSRF token
         */
        if($this->_configBackend->get('use_csrf_token') && Request::hasPost()){
            $csrf = new Security_Csrf();
            $csrf->setOptions(
                array(
                    'lifetime' => $this->_configBackend->get('use_csrf_token_lifetime'),
                    'cleanupLimit' => $this->_configBackend->get('use_csrf_token_garbage_limit')
                ));

            if(!$csrf->checkHeader() && !$csrf->checkPost())
                $this->_errorResponse($this->_lang->MSG_NEED_CSRF_TOKEN);
        }

        $this->_user = $user;

        $isSysController = in_array(get_called_class() , $this->_configBackend->get('system_controllers') , true);

        if($isSysController)
            return;

        if(! $this->_user->canView($this->_module))
            $this->_errorResponse($this->_lang->CANT_VIEW);

        $moduleManager = new Modules_Manager();

        /*
         * Redirect for undefined module
         */
        if(!$moduleManager->isValidModule($this->_module))
            $this->_errorResponse($this->_lang->WRONG_REQUEST);

        $moduleCfg = $moduleManager->getModuleConfig($this->_module);

        /*
         * Redirect for disabled module
         */
        if($moduleCfg['active'] == false)
            $this->_errorResponse($this->_lang->CANT_VIEW);

        /*
         * Redirect for dev module at prouction
         */
        if($moduleCfg['dev'] && ! $this->_configMain['development'])
            $this->_errorResponse($this->_lang->CANT_VIEW);
    }
    /**
     * Show login form
     */
    protected function loginAction()
    {
        $template = new Template();
        $template->set('wwwRoot' , $this->_configMain->get('wwwroot'));
        Response::put($template->render('system/'.$this->_configBackend->get('theme') . '/login.php'));
        exit;
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
        return $form->getData();
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

        $modulesConfig = Config::factory(Config\Factory::File_Array , $this->_configMain->get('backend_modules'));
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
        $manager = new Designer_Manager($this->_configMain);
        $project = $manager->findWorkingCopy($project);
        $manager->renderProject($project, $renderTo, $this->_module);
    }
    /**
     * Get desktop module info
     */
    protected function desktopModuleInfo()
    {
        $modulesConfig = Config::factory(Config::File_Array , $this->_configMain->get('backend_modules'));
        $moduleCfg = $modulesConfig->get($this->_module);

        $projectData = [];

        if(strlen($moduleCfg['designer']))
        {
            $manager = new Designer_Manager($this->_configMain);
            $project = $manager->findWorkingCopy($moduleCfg['designer']);
            $projectData =  $manager->compileDesktopProject($project, 'app.__modules.'.$this->_module , $this->_module);
            $projectData['isDesigner'] = true;
            $modulesManager = new Modules_Manager();
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

    public function showPage()
    {
        $request = Request::getInstance();
        $controllerCode = $request->getPart(1);
        $templatesPath = 'system/' . $this->_configBackend->get('theme') . '/';

        $page = \Page::getInstance();
        $page->setTemplatesPath($templatesPath);

        $wwwRoot = $this->_configMain->get('wwwroot');
        $adminPath = $this->_configMain->get('adminPath');
        $urlDelimiter = $this->_configMain->get('urlDelimiter');

        /*
         * Define frontend JS variables
         */
        $res = Resource::getInstance();
        $res->addInlineJs('
            app.wwwRoot = "' . $wwwRoot . '";
        	app.admin = "' . $request->url([$adminPath]) . '";
        	app.delimiter = "' . $urlDelimiter . '";
        	app.root = "' . $request->url([$adminPath, $controllerCode,'']) . '";
        ');

        $modulesManager = new \Modules_Manager();
        /*
         * Load template
         */
        $template = new Template();
        $template->disableCache();
        $template->setProperties(array(
            'wwwRoot' => $this->_configMain->get('wwwroot'),
            'page' => $page,
            'urlPath' => $controllerCode,
            'resource' => $res,
            'path' => $templatesPath,
            'adminPath' => $this->_configMain->get('adminPath'),
            'development' => $this->_configMain->get('development'),
            'version' => Config::storage()->get('versions.php')->get('core'),
            'lang' => $this->_configMain->get('language'),
            'modules' => $modulesManager->getList(),
            'userModules' => User::factory()->getModuleAcl()->getAvailableModules(),
            'useCSRFToken' => $this->_configBackend->get('use_csrf_token'),
            'theme' => $this->_configBackend->get('theme')
        ));

        Response::put($template->render($templatesPath . 'layout.php'));
    }
}