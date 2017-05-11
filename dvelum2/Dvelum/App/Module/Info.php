<?php
namespace Dvelum\App\Module;

use Dvelum\Config;

class Info
{
    protected $appConfig;

    public function __construct(Config\ConfigInterface $appConfig)
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
            $manager = new \Designer_Manager($this->appConfig);
            $project = $manager->findWorkingCopy($moduleCfg['designer']);
            $projectData =  $manager->compileDesktopProject($project, 'app.__modules.'.$module , $module);
            $projectData['isDesigner'] = true;
            $modulesManager = new \Modules_Manager();
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