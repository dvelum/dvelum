<?php
namespace Dvelum\App\Module;

use Dvelum\Config;
use Dvelum\Config\ConfigInterface;

class Info
{
    protected $appConfig;

    public function __construct(ConfigInterface $appConfig)
    {
        $this->appConfig = $appConfig;
    }

    public function desktopModuleInfo($module)
    {
        $modulesConfig = Config::factory(Config\Factory::File_Array , $this->appConfig->get('backend_modules'));
        $moduleCfg = $modulesConfig->get($module);

        $projectData = [];

        if(strlen($moduleCfg['designer']))
        {
            $manager = new \Dvelum\Designer\Manager($this->appConfig);
            $project = $manager->findWorkingCopy($moduleCfg['designer']);
            $projectData =  $manager->compileDesktopProject($project, 'app.__modules.'.$module , $module);
            $projectData['isDesigner'] = true;
            $modulesManager = $this->container->get(\Dvelum\App\Module\Manager::class);
            $modulesList = $modulesManager->getList();
            $projectData['title'] = (isset($modulesList[$module])) ? $modulesList[$module]['title'] : '';
        }
        else
        {
            if(file_exists($this->appConfig->get('jsPath').'app/system/desktop/' . strtolower($module) . '.js'))
                $projectData['includes']['js'][] = '/js/app/system/desktop/' . strtolower($module) .'.js';
        }
        return $projectData;
    }
}