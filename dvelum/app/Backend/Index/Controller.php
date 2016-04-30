<?php
/**
 * Default backoffice controller
 */
class Backend_Index_Controller extends Backend_Controller
{
    public function indexAction()
    {
        $this->includeScripts();
        if(!in_array($this->_configBackend->get('theme') , $this->_configBackend->get('desktop_themes') , true)){
            $this->_resource->addJs('js/app/system/crud/index.js', 4);
        }
    }

    /**
     * Get modules list
     */
    public function listAction()
    {
        $modulesManager = new Modules_Manager();
        $data = $modulesManager->getList();

        $modules = User::getInstance()->getAvailableModules();
        $data = Utils::sortByField($data  , 'title');

        $isDev = (boolean) $this->_configMain->get('development');

        $wwwRoot = $this->_configMain->get('wwwroot');
        $adminPath =  $this->_configMain->get('adminPath');

        $result = array();
        $devItems = array();
        foreach($data as $config)
        {
            if(!$config['active'] || !$config['in_menu'] || ($config['dev'] && !$isDev) || !isset($modules[$config['id']])){
                continue;
            }
            $item =[
                'id' => $config['id'],
                'icon'=> $wwwRoot.$config['icon'],
                'title'=> $config['title'],
                'url'=> Request::url([$adminPath , $config['id']]),
                'itemCls'=>$config['dev']?'dev':''
            ];
            if($config['dev']){
                $devItems[] = $item;
            }else{
                $result[] = $item;
            }

        }
        Response::jsonSuccess(array_merge($result,$devItems));
    }

    /**
     * Get module info
     */
    public function moduleInfoAction()
    {
        $module = Request::post('id' , Filter::FILTER_STRING , false);

        $manager = new Modules_Manager();
        $moduleCfg = $manager->getModuleConfig($module);

        $info = [];

        if(!$module || !$this->_user->canView($module) || !$moduleCfg['active']){
            Response::jsonError($this->_lang->get('CANT_VIEW'));
        }

        $controller = $moduleCfg['class'];

        if(!class_exists($controller)){
            Response::jsonError('Undefined controller');
        }

        $controller = new $controller();

        if(method_exists($controller,'desktopModuleInfo')){
            $info['layout'] = $controller->desktopModuleInfo();
        }else{
            $info['layout'] = false;
        }

        $info['permissions'] = $this->_user->getModulePermissions($module);
        Response::jsonSuccess($info);
    }
}