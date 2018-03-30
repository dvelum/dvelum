<?php
/*
* DVelum project http://code.google.com/p/dvelum/ , http://dvelum.net
* Copyright (C) 2011-2013  Kirill A Egorov
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
use Dvelum\Config\ConfigInterface;
use Dvelum\Lang;
use Dvelum\Service;

class Designer_Factory
{
    /**
     * Load project
     * @param ConfigInterface $designerConfig - designer config
     * @param string $projectFile - project file path
     * @return Designer_Project
     * @throws Exception
     */
    static public function loadProject(ConfigInterface $designerConfig , $projectFile)
    {
        $storage = Designer_Storage::getInstance($designerConfig->get('storage') , $designerConfig);
        return $storage->load($projectFile);
    }

    static public function importProject(ConfigInterface $designerConfig , $projectFile)
    {
        $storage = Designer_Storage::getInstance($designerConfig->get('storage') , $designerConfig);
        return $storage->import($projectFile);
    }

    /**
     * Get storage
     * @param ConfigInterface $designerConfig
     * @return Designer_Storage_Adapter_Abstract
     */
    static public function getStorage(ConfigInterface $designerConfig)
    {
        return Designer_Storage::getInstance($designerConfig->get('storage') , $designerConfig);
    }

    /**
     * Init layout from designer project
     * @property string $projectFile - designer project related path
     * @property ConfigInterface $designerConfig
     * @property array $replace, optional
     * @property string | boolean $renderTo
     * @property string | boolean $moduleId
     * @todo cache the code
     */
    static public function runProject($projectFile , ConfigInterface $designerConfig , $replace = array(), $renderTo = false, $moduleId = false)
    {
        /**
         * @todo slow operation
         */
        if(!file_exists($projectFile))
            throw new Exception('Invalid project file' . $projectFile);

        /**
         * @todo slow operation
         */
        $cachedKey = self::getProjectCacheKey($projectFile);

        $project = Designer_Factory::loadProject($designerConfig, $projectFile);
        $projectCfg = $project->getConfig();

        Ext_Code::setRunNamespace($projectCfg['runnamespace']);

        $projectData['includes'] = self::getProjectIncludes($cachedKey , $project , true , $replace);

        $names = $project->getRootPanels();

        $initCode = '
            var applicationClassesNamespace = "'.$projectCfg['namespace'].'";
            var applicationRunNamespace = "'.$projectCfg['runnamespace'].'";
        ';

        $initCode.= 'Ext.onReady(function(){';

        if(!empty($names))
        {
            if($renderTo){
                $renderTo = str_replace('-', '_', $renderTo);
                $initCode.= '
                app.content = Ext.create("Ext.container.Container", {
                    layout:"fit",
                    renderTo:"'.$renderTo.'"
                });
               ';
            }

            foreach ($names as $name)
            {
                if($project->getObject($name)->isExtendedComponent())
                {
                    /*if($project->getObject($name)->getConfig()->defineOnly)
                        continue;
                    */
                    $initCode.= Ext_Code::appendRunNamespace($name).' = Ext.create("'.Ext_Code::appendNamespace($name).'",{});';
                }
                $initCode.='
                    app.content.add('.Ext_Code::appendRunNamespace($name).');
                ';
            }

            if($renderTo)
            {
                $initCode.='
                    app.content.updateLayout();
                ';
            }
        }

        $initCode.='
                    app.application.fireEvent("projectLoaded", "'.$moduleId.'");
                });';

        $resource = \Dvelum\Resource::factory();

        if($projectData && isset($projectData['includes']) && !empty($projectData['includes']))
        {
            foreach ($projectData['includes'] as $file)
            {
                if(File::getExt($file) == '.css')
                {
                    if(strpos($file , '?') === false){
                        $file = $file .'?v='. $cachedKey;
                    }
                    $resource->addCss($file , 100);
                }else{

                    if(strpos($file , '?') === false){
                        $file = $file .'?v='. $cachedKey;
                    }

                    $resource->addJs($file , false, false);
                }
            }
        }
        $resource->addInlineJs($initCode);
    }

    /**
     * Init layout from designer project
     * @property string $projectFile - designer project related path
     * @property Config_Abstract $designerConfig
     * @property array $replaceTemplates, optional
     * @property string $renderTo
     * @property string $moduleId
     * @todo cache the code
     */
    static public function compileDesktopProject($projectFile , Config_Abstract $designerConfig , $replace, $renderTo, $moduleId)
    {
        $projectData = [
            'applicationClassesNamespace' =>false,
            'applicationRunNamespace' => false,
            'includes'=>[
                'js'=>[],
                'css'=>[]
            ]
        ];

        if(!file_exists($projectFile))
            throw new Exception('Invalid project file' . $projectFile);

        /**
         * @todo cache slow operation
         */
        $cachedKey = self::getProjectCacheKey($projectFile);

        $project = Designer_Factory::loadProject($designerConfig, $projectFile);
        $projectCfg = $project->getConfig();

        Ext_Code::setRunNamespace($projectCfg['runnamespace']);

        $projectData['applicationClassesNamespace'] = $projectCfg['namespace'];
        $projectData['applicationRunNamespace'] = $projectCfg['runnamespace'];

        $names = $project->getRootPanels();

        $initCode = '';

        if(!empty($names))
        {

            $renderTo = str_replace('-', '_', $renderTo);

            $items = [];

            $c=0;
            foreach ($names as $name)
            {
                // hide first panel title
                if($c==0)
                {
                    $panel = $project->getObject($name);
                    if($panel->isValidProperty('title') && !empty($panel->title)){
                        $panel->title = '';
                    }elseif($panel instanceof Ext_Object_Instance) {
                        $objConfig = $panel->getConfig();
                        $config = (!empty($objConfig->config)) ? $objConfig->config : array();
                        $objConfig->config = json_encode(array_merge($config,array('title'=>'')));
                    }
                    $c++;
                }
                $items[] = Ext_Code::appendRunNamespace($name);
            }

            $options = [];
            $moduleCfg = Config::storage()->get('desktop_module_options.php');
            if($moduleCfg->offsetExists($moduleId) && is_array($moduleCfg->get($moduleId)))
                $options = $moduleCfg->get($moduleId);

            $initCode.= $renderTo.' = Ext.create("app.cls.ModuleWindow", Ext.apply({items: ['
                .implode(',',$items).']},'.json_encode($options).'));';
        }

        $initCode.='
            Ext.onReady(function(){
                app.application.fireEvent("projectLoaded", "'.$moduleId.'");
            });
        ';

        $includes = self::getProjectIncludes($cachedKey , $project , true , $replace);

        if(!empty($includes))
        {
            $wwwRoot = Request::wwwRoot();
            foreach ($includes as $file)
            {
                if(File::getExt($file) == '.css')
                {
                    if(strpos($file , '?') === false){
                        $file = $file .'?'. $cachedKey;
                    }
                    $projectData['includes']['css'][]= str_replace('//','/',$wwwRoot.$file);
                }else{

                    if(strpos($file , '?') === false){
                        $file = $file .'?'. $cachedKey;
                    }
                    $projectData['includes']['js'][]= str_replace('//','/',$wwwRoot.$file);
                }
            }
        }
        $projectData['includes']['js'][] = \Dvelum\Resource::factory()->cacheJs($initCode , true);
        return $projectData;
    }

    /**
     * Gel list of JS files to include
     * (load and render designer project)
     * @param string $cacheKey
     * @param Designer_Project $project
     * @param boolean $selfInclude
     * @param array $replace
     * @param boolean $debug, optional default - false (no minification)
     * @return array
     */
    static public function getProjectIncludes($cacheKey , Designer_Project $project , $selfInclude = true , $replace = array() , $debug = false)
    {
        $applicationConfig = Config::storage()->get('main.php');
        $designerConfig = Config::storage()->get('designer.php');
        $manager = new Designer_Manager($applicationConfig);

        $projectConfig = $project->getConfig();

        $includes = array();

        // include langs
        if(isset($projectConfig['langs']) && !empty($projectConfig['langs']))
        {
            /**
             * @var Lang $langService
             */
            $langService = Service::get('lang');
            $language = $langService->getDefaultDictionary();
            $lansPath = $designerConfig->get('langs_path');
            $langsUrl = $designerConfig->get('langs_url');

            foreach ($projectConfig['langs'] as $k=>$file)
            {
                $file =  $language.'/'.$file.'.js';
                if(file_exists($lansPath.$file)){
                    $includes[] = $langsUrl . $file . '?' . filemtime($lansPath.$file);
                }
            }
        }

        if(isset($projectConfig['files']) && !empty($projectConfig['files']))
        {
            foreach ($projectConfig['files'] as $file)
            {
                $ext = File::getExt($file);

                if($ext === '.js' || $ext === '.css')
                {
                    $includes[] = $file;
                }else
                {
                    $projectFile = $manager->findWorkingCopy($file);
                    $subProject = Designer_Factory::loadProject($designerConfig,  $projectFile);
                    $projectKey = self::getProjectCacheKey($projectFile);
                    $files = self::getProjectIncludes($projectKey , $subProject , true , $replace , $debug);
                    unset($subProject);
                    if(!empty($files))
                        $includes = array_merge($includes , $files);
                }
            }
        }

        Ext_Code::setRunNamespace($projectConfig['runnamespace']);
        Ext_Code::setNamespace($projectConfig['namespace']);

        if($selfInclude)
        {
            $layoutCacheFile = Utils::createCachePath($applicationConfig->get('jsCachePath'), $cacheKey.'.js');

            /**
             * @todo remove slow operation
             */
            if(!file_exists($layoutCacheFile)){
                if($debug){
                    file_put_contents($layoutCacheFile, $project->getCode($replace));
                } else {
                    file_put_contents($layoutCacheFile, \Dvelum\App\Code\Minify\Minify::factory()->minifyJs($project->getCode($replace)));
                }
            }
            $includes[] = '/'.str_replace($applicationConfig->get('jsCachePath'), $applicationConfig->get('jsCacheUrl') , $layoutCacheFile);
        }
        return $includes;
    }
    /**
     * Calculate cache key for Designer Project file
     * @param string $projectFile
     * @return string
     */
    static public function getProjectCacheKey($projectFile)
    {
        /**
         * @todo remove slow operation
         */
        $dManager = Dictionary_Manager::factory();
        return md5(@filemtime($projectFile) . $projectFile . $dManager->getDataHash());
    }
    /**
     * Replace code templates
     * @param array $replaces
     * @param string $code
     * @return string
     */
    static public function replaceCodeTemplates(array $replaces , $code)
    {
        if(!empty($replaces))
        {
            $k = array();
            $v = array();
            foreach ($replaces as $item)
            {
                $k[] = $item['tpl'];
                $v[] = $item['value'];
            }
            return str_replace($k , $v , $code);
        }
        return $code;
    }
}