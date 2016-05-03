<?php
/*
 * DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net Copyright
 * (C) 2011-2013 Kirill A Egorov This program is free software: you can
 * redistribute it and/or modify it under the terms of the GNU General Public
 * License as published by the Free Software Foundation, either version 3 of the
 * License, or (at your option) any later version. This program is distributed
 * in the hope that it will be useful, but WITHOUT ANY WARRANTY; without even
 * the implied warranty of MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.
 * See the GNU General Public License for more details. You should have received
 * a copy of the GNU General Public License along with this program. If not, see
 * <http://www.gnu.org/licenses/>.
 */

abstract class Frontend_Controller_Backoffice extends Backend_Controller
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
    protected $_configBackoffice;

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

    public function __construct()
    {
        $this->_page = Page::getInstance();
        $this->_resource = Resource::getInstance();
        $this->_module = $this->getModule();
        $this->_lang = Lang::lang();
        $this->_configMain = Registry::get('main' , 'config');
        
        $cacheManager = new Cache_Manager();
        $this->_configBackoffice = Config::storage()->get('backend.php');
        $this->_cache = $cacheManager->get('data');
        
        if(Request::get('logout' , 'boolean' , false)){
            User::getInstance()->logout();
            session_destroy();
            if(!Request::isAjax())
                Response::redirect(Request::url(array('index')));
        }
        $this->checkAuth();
        
        if($this->_configBackoffice->get('use_csrf_token')){
          $csrf = new Security_Csrf();
          $this->_page->csrfToken = $csrf->createToken();
        }
    }

    /**
     * Include required JavaScript files defined in the configuration file
     */
    public function includeScripts()
    {
        $media = Model::factory('Medialib');
        $media->includeScripts();
        $cfg = Config::storage()->get('js_inc_backend.php');
        
        $this->_resource->addJs('/js/lib/jquery.js',0 , true , 'head');

        $this->_resource->addJs('/js/lib/extjs4/ext-all-debug.js' , 0 , true , 'head');
        $this->_resource->addJs('/js/lang/'.$this->_configMain['language'].'.js', 1 , true , 'head');
        $this->_resource->addJs('/js/lib/extjs4/locale/ext-lang-'.$this->_configMain['language'].'.js', 2 , true , 'head');
        $this->_resource->addJs('/js/app/frontend/application.js', 3 , false ,  'head');
        $this->_resource->addJs('/js/app/system/common.js', 3 , false ,  'head');

        $this->_resource->addJs('/js/lib/extjs4/ext-theme-gray.js' , 2);
        $this->_resource->addCss('/js/lib/extjs4/resources/css/ext-all-gray.css');
        $this->_resource->addCss('/css/system/default/style.css');

        $this->_resource->addInlineJs('
           var developmentMode = '.intval($this->_configMain->get('development')).';
           app.wwwRoot = "' . $this->_configMain->get('wwwroot') . '";    
           app.delimiter = "'.$this->_configMain->get('urlDelimiter') . '";
           app.admin = "' . $this->_configMain->get('wwwroot') .  $this->_configMain->get('adminPath').'";
        ');

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
     * Check user permissions and authentication
     */
    public function checkAuth()
    {
        $user = User::getInstance();
        $uid = false;
        
        if($user->isAuthorized())
            $uid = $user->id;
        
        if(! $uid || ! $user->isAdmin()){
            if(Request::isAjax())
                Response::jsonError($this->_lang->MSG_AUTHORIZE);
            else
                $this->loginAction();
        }
        /*
         * Check CSRF token
         */
        if($this->_configBackoffice->get('use_csrf_token') && Request::hasPost()){
            $csrf = new Security_Csrf();
            $csrf->setOptions(
                    array(
                            'lifetime' => $this->_configBackoffice->get('use_csrf_token_lifetime'),
                            'cleanupLimit' => $this->_configBackoffice->get('use_csrf_token_garbage_limit')
                    ));
            
            if(!$csrf->checkHeader() && !$csrf->checkPost())
                $this->_errorResponse($this->_lang->MSG_NEED_CSRF_TOKEN);
        }
        
        $this->_user = $user;
    }

    /**
     * Show login form
     */
    protected function loginAction()
    {
        $template = new Template();
        $template->set('wwwRoot' , $this->_configMain->get('wwwroot'));
        Response::put($template->render('public/backoffice_login.php'));
        Application::close();
    }

    public function indexAction()
    {
      $this->_resource->addInlineJs('
	     var canEdit = ' . intval($this->_user->canEdit($this->_module)) . ';
	     var canDelete = ' . intval($this->_user->canDelete($this->_module)) . ';
         var canPublish = ' . intval($this->_user->canPublish($this->_module)) . ';
	    ');
      $this->includeScripts();
    }

    /**
     * Run Layout project
     * 
     * @param string $project - path to project file
     */
    protected function _runDesignerProject($project, $renderTo = false)
    {    
      $manager = new Designer_Manager($this->_configMain);
      $manager->renderProject($project , $renderTo);
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
            Response::redirect(Request::url(array('index')));
    }
}