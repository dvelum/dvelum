<?php
/**
 * Default backoffice controller
 */
use Dvelum\Orm;
use Dvelum\Orm\Model;
use Dvelum\Config;


class Backend_Index_Controller extends Dvelum\App\Backend\Controller
{
    public function getModule() :?string
    {
        return 'index';
    }

    public function indexAction()
    {
        $config = Config::storage()->get('backend.php');
        $this->includeScripts();
        if(!in_array($config->get('theme') , $config->get('desktop_themes') , true)){
            $this->resource->addJs('js/app/system/crud/index.js', 4);
        }
    }

    /**
     * Get modules list
     */
    public function listAction()
    {
        $modulesManager = new Modules_Manager();

        $data = $modulesManager->getList();

        $modules = $this->user->getModuleAcl()->getAvailableModules();

        $data = \Utils::sortByField($data  , 'title');

        $isDev = (boolean) $this->appConfig->get('development');
        $wwwRoot = $this->appConfig->get('wwwroot');
        $adminPath =  $this->appConfig->get('adminPath');

        $result = [];
        $devItems = [];

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
        $this->response->success(array_merge($result,$devItems));
    }

    /**
     * Get module info
     */
    public function moduleInfoAction()
    {
        $module = $this->request->post('id' , Filter::FILTER_STRING , false);

        $manager = new Modules_Manager();
        $moduleCfg = $manager->getModuleConfig($module);

        $info = [];

        if(!$module || !$this->user->getModuleAcl()->canView($module) || !$moduleCfg['active']){
            $this->response->error($this->lang->get('CANT_VIEW'));
        }

        $controller = $moduleCfg['class'];

        if(!class_exists($controller)){
            $this->response->error('Undefined controller');
        }

        $controller = new $controller();

        if(method_exists($controller,'desktopModuleInfo')){
            $info['layout'] = $controller->desktopModuleInfo();
        }else{
            $info['layout'] = false;
        }

        $info['permissions'] = $this->user->getModuleAcl()->getModulePermissions($module);
        $this->response->success($info);
    }
}