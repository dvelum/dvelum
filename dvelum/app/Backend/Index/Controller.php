<?php
class Backend_Index_Controller extends Backend_Controller
{  
    public function indexAction()
    {
    	$this->_resource->addJs('js/app/system/crud/index.js', 4);
    }

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
}